/*
 * 360MiQ Pine-compatible runtime.
 *
 * This is intentionally a restricted language runtime. It parses Pine-like
 * indicator scripts into an AST and evaluates series in the browser without
 * executing JavaScript supplied by the user.
 */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.PineScriptRuntime = factory();
  }
}(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var VERSION = '0.3.0';
  var MAX_SOURCE_LENGTH = 120000;
  var MAX_PLOTS = 64;
  var MAX_LOOPS = 1000;
  var MAX_FUNCTION_DEPTH = 32;
  var MAX_SECURITY_REQUESTS = 32;
  var MAX_OPERATIONS = 500000;
  var COMPILE_CACHE_LIMIT = 32;
  var compileCache = Object.create(null);
  var compileCacheKeys = [];
  var activeBudget = null;

  function consumeBudget(amount, line) {
    if (!activeBudget) return;
    activeBudget.used += amount || 1;
    if (activeBudget.used > activeBudget.max) {
      throw new PineError('The script exceeded the maximum execution budget of ' + activeBudget.max + ' operations.', line || 1, 1);
    }
  }

  function PineError(message, line, column) {
    this.name = 'PineError';
    this.message = message;
    this.line = line || 1;
    this.column = column || 1;
    if (Error.captureStackTrace) Error.captureStackTrace(this, PineError);
  }
  PineError.prototype = Object.create(Error.prototype);
  PineError.prototype.constructor = PineError;
  PineError.prototype.toString = function () {
    return 'Line ' + this.line + ', column ' + this.column + ': ' + this.message;
  };

  function isFiniteNumber(value) {
    return typeof value === 'number' && Number.isFinite(value);
  }

  function asNumber(value) {
    if (value == null || value === false || value === '') return NaN;
    var number = Number(value);
    return Number.isFinite(number) ? number : NaN;
  }

  function asBool(value) {
    if (value instanceof PineSeries) return value;
    return !!value;
  }

  function valueAt(value, index) {
    return value instanceof PineSeries ? value.get(index) : value;
  }

  function mapSeries(source, mapper, label) {
    var series = new PineSeries(function (index) {
      return mapper(valueAt(source, index), index);
    }, label);
    return series;
  }

  function binarySeries(left, right, mapper, label) {
    if (!(left instanceof PineSeries) && !(right instanceof PineSeries)) return mapper(left, right, 0);
    return new PineSeries(function (index) {
      return mapper(valueAt(left, index), valueAt(right, index), index);
    }, label);
  }

  function PineSeries(getter, label) {
    this.getter = typeof getter === 'function' ? getter : function () { return NaN; };
    this.label = label || '';
    this.cache = Object.create(null);
  }

  PineSeries.prototype.get = function (index) {
    index = Math.floor(Number(index));
    if (!Number.isFinite(index) || index < 0) return NaN;
    var key = String(index);
    if (!Object.prototype.hasOwnProperty.call(this.cache, key)) {
      consumeBudget(1, 1);
      this.cache[key] = this.getter(index);
    }
    return this.cache[key];
  };

  PineSeries.prototype.at = function (index, offset) {
    return this.get(index - Math.max(0, Number(offset) || 0));
  };

  function tokenize(source) {
    var tokens = [];
    var index = 0;
    var line = 1;
    var column = 1;
    var length = source.length;
    var lineStart = true;
    var indentStack = [0];
    function push(type, value, tokenLine, tokenColumn) {
      tokens.push({ type: type, value: value, line: tokenLine, column: tokenColumn });
    }
    function advance(count) {
      count = count || 1;
      for (var i = 0; i < count; i += 1) {
        if (source.charAt(index) === '\n') { line += 1; column = 1; }
        else column += 1;
        index += 1;
      }
    }
    while (index < length) {
      if (lineStart) {
        var indentation = 0;
        while (index < length && (source.charAt(index) === ' ' || source.charAt(index) === '\t')) {
          indentation += source.charAt(index) === '\t' ? 4 : 1;
          advance();
        }
        character = source.charAt(index);
        if (character === '\n') {
          push('newline', '\n', line, column);
          advance();
          lineStart = true;
          continue;
        }
        if (character === '/' && source.charAt(index + 1) === '/') {
          while (index < length && source.charAt(index) !== '\n') advance();
          continue;
        }
        var currentIndent = indentStack[indentStack.length - 1];
        if (indentation > currentIndent) {
          indentStack.push(indentation);
          push('indent', indentation, line, column);
        } else if (indentation < currentIndent) {
          while (indentStack.length > 1 && indentation < indentStack[indentStack.length - 1]) {
            indentStack.pop();
            push('dedent', indentation, line, column);
          }
          if (indentation !== indentStack[indentStack.length - 1]) {
            throw new PineError('Inconsistent indentation.', line, column);
          }
        }
        lineStart = false;
      }
      var character = source.charAt(index);
      if (character === ' ' || character === '\t' || character === '\r') { advance(); continue; }
      if (character === '\n') { push('newline', '\n', line, column); advance(); lineStart = true; continue; }
      if (character === '/' && source.charAt(index + 1) === '/') {
        while (index < length && source.charAt(index) !== '\n') advance();
        continue;
      }
      var tokenLine = line;
      var tokenColumn = column;
      if (character === '"' || character === "'") {
        var quote = character;
        var value = '';
        advance();
        while (index < length && source.charAt(index) !== quote) {
          if (source.charAt(index) === '\\' && index + 1 < length) {
            advance();
            value += source.charAt(index);
            advance();
          } else {
            value += source.charAt(index);
            advance();
          }
        }
        if (source.charAt(index) !== quote) throw new PineError('Unterminated string.', tokenLine, tokenColumn);
        advance();
        push('string', value, tokenLine, tokenColumn);
        continue;
      }
      if (/[0-9.]/.test(character) && (/[0-9]/.test(character) || /[0-9]/.test(source.charAt(index + 1)))) {
        var numberStart = index;
        while (index < length && /[0-9.eE+-]/.test(source.charAt(index))) {
          if ((source.charAt(index) === '+' || source.charAt(index) === '-') && !/[eE]/.test(source.charAt(index - 1))) break;
          advance();
        }
        var numberText = source.slice(numberStart, index);
        var number = Number(numberText);
        if (!Number.isFinite(number)) throw new PineError('Invalid number "' + numberText + '".', tokenLine, tokenColumn);
        push('number', number, tokenLine, tokenColumn);
        continue;
      }
      if (/[A-Za-z_]/.test(character)) {
        var identifierStart = index;
        while (index < length && /[A-Za-z0-9_]/.test(source.charAt(index))) advance();
        push('identifier', source.slice(identifierStart, index), tokenLine, tokenColumn);
        continue;
      }
      var two = source.slice(index, index + 2);
      if (['<=', '>=', '==', '!=', ':=', '=>'].indexOf(two) !== -1) {
        push('operator', two, tokenLine, tokenColumn);
        advance(2);
        continue;
      }
      if ('+-*/%<>=!?:,.()[]{}'.indexOf(character) !== -1) {
        push('operator', character, tokenLine, tokenColumn);
        advance();
        continue;
      }
      throw new PineError('Unexpected character "' + character + '".', tokenLine, tokenColumn);
    }
    while (indentStack.length > 1) {
      indentStack.pop();
      push('dedent', '', line, column);
    }
    push('eof', '', line, column);
    return tokens;
  }

  function Parser(tokens) {
    this.tokens = tokens;
    this.index = 0;
  }

  Parser.prototype.current = function () { return this.tokens[this.index]; };
  Parser.prototype.peek = function (offset) { return this.tokens[this.index + (offset || 1)] || this.tokens[this.tokens.length - 1]; };
  Parser.prototype.match = function (value) { return this.current().value === value; };
  Parser.prototype.take = function () { return this.tokens[this.index++]; };
  Parser.prototype.expect = function (value) {
    var token = this.take();
    if (!token || token.value !== value) throw new PineError('Expected "' + value + '".', token && token.line, token && token.column);
    return token;
  };
  Parser.prototype.skipNewlines = function () {
    while (this.current().type === 'newline') this.take();
  };

  Parser.prototype.parseProgram = function () {
    var statements = [];
    this.skipNewlines();
    while (this.current().type !== 'eof') {
      if (this.current().type === 'dedent') {
        this.take();
        continue;
      }
      statements.push(this.parseStatement());
      while (this.match(';') || this.current().type === 'newline') this.take();
    }
    return statements;
  };

  Parser.prototype.parseIndentedBlock = function () {
    if (this.current().type === 'newline') this.take();
    this.skipNewlines();
    if (this.current().type !== 'indent') {
      throw new PineError('Expected an indented block.', this.current().line, this.current().column);
    }
    this.take();
    return this.parseBlockStatements();
  };

  Parser.prototype.parseBlockStatements = function () {
    var statements = [];
    this.skipNewlines();
    while (this.current().type !== 'dedent' && this.current().type !== 'eof') {
      statements.push(this.parseStatement());
      while (this.match(';') || this.current().type === 'newline') this.take();
      this.skipNewlines();
    }
    if (this.current().type === 'dedent') this.take();
    return statements;
  };

  Parser.prototype.parseBlockBody = function (line, description) {
    if (this.current().type !== 'newline') {
      throw new PineError('Expected a new line after ' + description + '.', line, 1);
    }
    return this.parseIndentedBlock();
  };

  Parser.prototype.parseStatement = function () {
    var token = this.current();
    var functionDeclaration = this.parseFunctionDeclaration();
    if (functionDeclaration) return functionDeclaration;
    if (token.type === 'identifier' && token.value === 'if') {
      this.take();
      var test = this.parseExpression(0);
      var consequent = this.parseBlockBody(token.line, 'if');
      var alternate = null;
      if (this.current().type === 'identifier' && this.current().value === 'else') {
        this.take();
        if (this.current().type === 'identifier' && this.current().value === 'if') alternate = [this.parseStatement()];
        else alternate = this.parseBlockBody(token.line, 'else');
      }
      return { type: 'if', test: test, consequent: consequent, alternate: alternate, line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'for') {
      this.take();
      var loopVariable = this.take();
      if (!loopVariable || loopVariable.type !== 'identifier') throw new PineError('Expected a loop variable.', token.line, token.column);
      this.expect('=');
      var loopStart = this.parseExpression(0);
      var rangeKeyword = this.take();
      if (!rangeKeyword || rangeKeyword.type !== 'identifier' || rangeKeyword.value !== 'to') throw new PineError('Expected "to" in for loop.', rangeKeyword && rangeKeyword.line, rangeKeyword && rangeKeyword.column);
      var loopEnd = this.parseExpression(0);
      return { type: 'for', name: loopVariable.value, start: loopStart, end: loopEnd, body: this.parseBlockBody(token.line, 'for'), line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'return') {
      this.take();
      return { type: 'return', expression: this.current().type === 'newline' ? null : this.parseExpression(0), line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'break') {
      this.take();
      return { type: 'break', line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'var') {
      this.take();
      if (this.current().type === 'identifier' && ['int', 'float', 'bool', 'string', 'color'].indexOf(this.current().value) !== -1) this.take();
      var varName = this.take();
      if (!varName || varName.type !== 'identifier') throw new PineError('Expected a variable name.', token.line, token.column);
      this.expect('=');
      return { type: 'assignment', name: varName.value, expression: this.parseExpression(0), persistent: true, line: token.line };
    }
    if (token.type === 'identifier' && (token.value === 'indicator' || token.value === 'study' || token.value === 'strategy')) {
      var declaration = this.parseExpression(0);
      return { type: 'declaration', expression: declaration, line: token.line };
    }
    if (token.type === 'identifier' && (this.peek().value === '=' || this.peek().value === ':=')) {
      var name = this.take();
      var assignmentOperator = this.take().value;
      return { type: 'assignment', name: name.value, expression: this.parseExpression(0), persistent: assignmentOperator === ':=', line: name.line };
    }
    if (this.match('[')) {
      var tuple = this.parseTupleTarget();
      var tupleOperator = this.take();
      if (!tupleOperator || (tupleOperator.value !== '=' && tupleOperator.value !== ':=')) throw new PineError('Expected assignment after tuple.', tupleOperator && tupleOperator.line, tupleOperator && tupleOperator.column);
      return { type: 'tuple-assignment', names: tuple, expression: this.parseExpression(0), persistent: tupleOperator.value === ':=', line: token.line };
    }
    return { type: 'expression', expression: this.parseExpression(0), line: token.line };
  };

  Parser.prototype.parseFunctionDeclaration = function () {
    var start = this.index;
    var name = this.current();
    if (!name || name.type !== 'identifier' || this.peek().value !== '(') return null;
    this.take();
    if (!this.match('(')) {
      this.index = start;
      return null;
    }
    this.take();
    var parameters = [];
    while (!this.match(')')) {
      var parameter = this.take();
      if (!parameter || parameter.type !== 'identifier') {
        this.index = start;
        return null;
      }
      parameters.push(parameter.value);
      if (this.match(',')) {
        this.take();
      } else if (!this.match(')')) {
        this.index = start;
        return null;
      }
    }
    if (!this.match(')')) {
      this.index = start;
      return null;
    }
    this.take();
    if (!this.match('=>')) {
      this.index = start;
      return null;
    }
    this.take();
    if (this.current().type === 'newline') {
      return {
        type: 'function',
        name: name.value,
        parameters: parameters,
        body: this.parseIndentedBlock(),
        line: name.line
      };
    }
    return {
      type: 'function',
      name: name.value,
      parameters: parameters,
      expression: this.parseExpression(0),
      line: name.line
    };
  };

  Parser.prototype.parseTupleTarget = function () {
    var names = [];
    this.expect('[');
    while (!this.match(']')) {
      var token = this.take();
      if (!token || token.type !== 'identifier') throw new PineError('Expected a tuple variable name.', token && token.line, token && token.column);
      names.push(token.value);
      if (!this.match(',')) break;
      this.take();
    }
    this.expect(']');
    return names;
  };

  Parser.prototype.parseExpression = function (minimumPrecedence) {
    var left = this.parsePrefix();
    var precedence = {
      'or': 1, 'and': 2, '==': 3, '!=': 3, '<': 4, '<=': 4, '>': 4, '>=': 4,
      '+': 5, '-': 5, '*': 6, '/': 6, '%': 6
    };
    while (true) {
      var token = this.current();
      if (token.value === '(') {
        this.take();
        var callArgs = [];
        while (!this.match(')')) {
          var callName = null;
          if (this.current().type === 'identifier' && this.peek().value === '=') {
            callName = this.take().value;
            this.take();
          }
          callArgs.push({ name: callName, value: this.parseExpression(0) });
          if (!this.match(',')) break;
          this.take();
        }
        this.expect(')');
        left = { type: 'call', callee: left, args: callArgs, line: token.line };
        continue;
      }
      if (token.value === '.' || token.value === '[') {
        if (token.value === '.') {
          this.take();
          var member = this.take();
          if (!member || member.type !== 'identifier') throw new PineError('Expected a member name.', token.line, token.column);
          left = { type: 'member', object: left, property: member.value, line: token.line };
        } else {
          this.take();
          var index = this.parseExpression(0);
          this.expect(']');
          left = { type: 'index', object: left, index: index, line: token.line };
        }
        continue;
      }
      if (token.value === '?') {
        if (1 < minimumPrecedence) break;
        this.take();
        var consequent = this.parseExpression(0);
        this.expect(':');
        var alternate = this.parseExpression(1);
        left = { type: 'conditional', test: left, consequent: consequent, alternate: alternate, line: token.line };
        continue;
      }
      var operator = token.value === 'and' || token.value === 'or' ? token.value : token.value;
      var currentPrecedence = precedence[operator];
      if (currentPrecedence == null || currentPrecedence < minimumPrecedence) break;
      this.take();
      var right = this.parseExpression(currentPrecedence + 1);
      left = { type: 'binary', operator: operator, left: left, right: right, line: token.line };
    }
    return left;
  };

  Parser.prototype.parsePrefix = function () {
    var token = this.take();
    if (!token) throw new PineError('Unexpected end of script.');
    if (token.type === 'number' || token.type === 'string') return { type: 'literal', value: token.value, line: token.line };
    if (token.type === 'identifier') {
      if (token.value === 'true') return { type: 'literal', value: true, line: token.line };
      if (token.value === 'false') return { type: 'literal', value: false, line: token.line };
      if (token.value === 'na') return { type: 'literal', value: NaN, line: token.line };
      var node = { type: 'identifier', name: token.value, line: token.line };
      if (this.match('(')) {
        this.take();
        var args = [];
        while (!this.match(')')) {
          var name = null;
          if (this.current().type === 'identifier' && this.peek().value === '=') {
            name = this.take().value;
            this.take();
          }
          args.push({ name: name, value: this.parseExpression(0) });
          if (!this.match(',')) break;
          this.take();
        }
        this.expect(')');
        node = { type: 'call', callee: node, args: args, line: token.line };
      }
      return node;
    }
    if (token.value === '(') {
      var expression = this.parseExpression(0);
      this.expect(')');
      return expression;
    }
    if (token.value === '+' || token.value === '-' || token.value === '!') {
      return { type: 'unary', operator: token.value, argument: this.parseExpression(7), line: token.line };
    }
    throw new PineError('Unexpected token "' + token.value + '".', token.line, token.column);
  };

  function parse(source) {
    source = String(source == null ? '' : source);
    if (source.length > MAX_SOURCE_LENGTH) throw new PineError('Script is too large. Maximum length is ' + MAX_SOURCE_LENGTH + ' characters.');
    var cleanSource = source.replace(/^\s*\/\/\@version\s*=.*$/gim, '');
    return new Parser(tokenize(cleanSource)).parseProgram();
  }

  function argumentMap(args, env, index) {
    var positional = [];
    var named = {};
    (args || []).forEach(function (arg) {
      var value = evaluate(arg.value, env, index);
      if (arg.name) named[arg.name] = value;
      else positional.push(value);
    });
    positional.named = named;
    return positional;
  }

  function callFunction(fn, args, env, index, line) {
    if (typeof fn !== 'function') throw new PineError('Value is not callable.', line, 1);
    try { return fn(args, env, index); }
    catch (error) {
      if (error instanceof PineError) throw error;
      throw new PineError(error && error.message ? error.message : String(error), line, 1);
    }
  }

  function hasEnvironmentValue(env, name) {
    var scope = env;
    while (scope && scope !== Object.prototype) {
      if (Object.prototype.hasOwnProperty.call(scope, name)) return true;
      scope = Object.getPrototypeOf ? Object.getPrototypeOf(scope) : null;
    }
    return false;
  }

  function evaluate(node, env, index) {
    consumeBudget(1, node && node.line);
    if (!node) return NaN;
    if (node.type === 'literal') return node.value;
    if (node.type === 'identifier') {
      if (hasEnvironmentValue(env, node.name)) return env[node.name];
      throw new PineError('Unknown identifier "' + node.name + '".', node.line, 1);
    }
    if (node.type === 'member') {
      var object = evaluate(node.object, env, index);
      if (object == null) throw new PineError('Cannot read member "' + node.property + '".', node.line, 1);
      return object[node.property];
    }
    if (node.type === 'index') {
      var indexed = evaluate(node.object, env, index);
      var offset = evaluate(node.index, env, index);
      if (indexed instanceof PineSeries) return indexed.at(index, offset);
      if (Array.isArray(indexed)) return indexed[Math.max(0, Math.floor(Number(offset) || 0))];
      return NaN;
    }
    if (node.type === 'call') {
      if (node.callee && node.callee.type === 'member' && node.callee.object && node.callee.object.type === 'identifier' &&
        node.callee.object.name === 'request' && node.callee.property === 'security' && typeof env.__requestSecurity === 'function') {
        return env.__requestSecurity(node.args || [], env, index, node);
      }
      var fn = evaluate(node.callee, env, index);
      return callFunction(fn, argumentMap(node.args, env, index), env, index, node.line);
    }
    if (node.type === 'unary') {
      var value = evaluate(node.argument, env, index);
      if (node.operator === '!') return value instanceof PineSeries ? mapSeries(value, function (item) { return !item; }) : !value;
      return node.operator === '-' ? (value instanceof PineSeries ? mapSeries(value, function (item) { return -asNumber(item); }) : -asNumber(value)) : value;
    }
    if (node.type === 'conditional') {
      var test = evaluate(node.test, env, index);
      var consequent = evaluate(node.consequent, env, index);
      var alternate = evaluate(node.alternate, env, index);
      return test instanceof PineSeries || consequent instanceof PineSeries || alternate instanceof PineSeries
        ? new PineSeries(function (barIndex) { return valueAt(test, barIndex) ? valueAt(consequent, barIndex) : valueAt(alternate, barIndex); })
        : (test ? consequent : alternate);
    }
    if (node.type === 'binary') {
      var left = evaluate(node.left, env, index);
      var right = evaluate(node.right, env, index);
      var operator = node.operator;
      var mapper = function (a, b) {
        if (operator === 'and') return !!a && !!b;
        if (operator === 'or') return !!a || !!b;
        if (operator === '==') return a === b;
        if (operator === '!=') return a !== b;
        if (operator === '<') return asNumber(a) < asNumber(b);
        if (operator === '<=') return asNumber(a) <= asNumber(b);
        if (operator === '>') return asNumber(a) > asNumber(b);
        if (operator === '>=') return asNumber(a) >= asNumber(b);
        if (operator === '+') return asNumber(a) + asNumber(b);
        if (operator === '-') return asNumber(a) - asNumber(b);
        if (operator === '*') return asNumber(a) * asNumber(b);
        if (operator === '/') { var denominator = asNumber(b); return denominator === 0 ? NaN : asNumber(a) / denominator; }
        if (operator === '%') { var divisor = asNumber(b); return divisor === 0 ? NaN : asNumber(a) % divisor; }
        return NaN;
      };
      return binarySeries(left, right, mapper);
    }
    throw new PineError('Unsupported expression.', node.line, 1);
  }

  function lengthArgument(args, index, fallback) {
    var value = args.named.length != null ? args.named.length : args[1];
    value = Math.floor(asNumber(value));
    return Math.max(1, Number.isFinite(value) ? value : fallback || 14);
  }

  function sourceArgument(args, index) {
    return args.named.source != null ? args.named.source : args[0];
  }

  function rollingSma(source, length) {
    return new PineSeries(function (index) {
      var total = 0;
      for (var i = 0; i < length; i += 1) {
        var value = asNumber(valueAt(source, index - i));
        if (!Number.isFinite(value)) return NaN;
        total += value;
      }
      return total / length;
    });
  }

  function rollingSum(source, length) {
    return new PineSeries(function (index) {
      var total = 0;
      for (var i = 0; i < length; i += 1) {
        var value = asNumber(valueAt(source, index - i));
        if (!Number.isFinite(value)) return NaN;
        total += value;
      }
      return total;
    });
  }

  function rollingEma(source, length) {
    var alpha = 2 / (length + 1);
    var ema = new PineSeries(function (index) {
      if (index < length - 1) return NaN;
      var previous = ema.get(index - 1);
      var value = asNumber(valueAt(source, index));
      if (!Number.isFinite(value)) return NaN;
      if (!Number.isFinite(previous)) return rollingSma(source, length).get(index);
      return value * alpha + previous * (1 - alpha);
    });
    return ema;
  }

  function rollingRma(source, length) {
    var alpha = 1 / length;
    var rma = new PineSeries(function (index) {
      if (index < length - 1) return NaN;
      var previous = rma.get(index - 1);
      var value = asNumber(valueAt(source, index));
      if (!Number.isFinite(value)) return NaN;
      if (!Number.isFinite(previous)) return rollingSma(source, length).get(index);
      return value * alpha + previous * (1 - alpha);
    });
    return rma;
  }

  function rollingHighest(source, length) {
    return new PineSeries(function (index) {
      var result = -Infinity;
      for (var i = 0; i < length; i += 1) {
        var value = asNumber(valueAt(source, index - i));
        if (!Number.isFinite(value)) return NaN;
        result = Math.max(result, value);
      }
      return result;
    });
  }

  function rollingLowest(source, length) {
    return new PineSeries(function (index) {
      var result = Infinity;
      for (var i = 0; i < length; i += 1) {
        var value = asNumber(valueAt(source, index - i));
        if (!Number.isFinite(value)) return NaN;
        result = Math.min(result, value);
      }
      return result;
    });
  }

  function standardDeviation(source, length) {
    return new PineSeries(function (index) {
      var values = [];
      for (var i = 0; i < length; i += 1) {
        var value = asNumber(valueAt(source, index - i));
        if (!Number.isFinite(value)) return NaN;
        values.push(value);
      }
      var mean = values.reduce(function (sum, value) { return sum + value; }, 0) / length;
      return Math.sqrt(values.reduce(function (sum, value) { return sum + Math.pow(value - mean, 2); }, 0) / length);
    });
  }

  function computeRsi(source, length) {
    var gains = mapSeries(source, function (value, index) {
      var previous = valueAt(source, index - 1);
      return Number.isFinite(asNumber(value)) && Number.isFinite(asNumber(previous)) ? Math.max(0, asNumber(value) - asNumber(previous)) : NaN;
    });
    var losses = mapSeries(source, function (value, index) {
      var previous = valueAt(source, index - 1);
      return Number.isFinite(asNumber(value)) && Number.isFinite(asNumber(previous)) ? Math.max(0, asNumber(previous) - asNumber(value)) : NaN;
    });
    var averageGain = rollingRma(gains, length);
    var averageLoss = rollingRma(losses, length);
    return new PineSeries(function (index) {
      var gain = averageGain.get(index);
      var loss = averageLoss.get(index);
      if (!Number.isFinite(gain) || !Number.isFinite(loss)) return NaN;
      if (loss === 0) return gain === 0 ? 50 : 100;
      return 100 - (100 / (1 + gain / loss));
    });
  }

  function createNamespaces() {
    var ta = {
      sma: function (args) { return rollingSma(sourceArgument(args), lengthArgument(args, 0, 14)); },
      ema: function (args) { return rollingEma(sourceArgument(args), lengthArgument(args, 0, 14)); },
      rma: function (args) { return rollingRma(sourceArgument(args), lengthArgument(args, 0, 14)); },
      wma: function (args) {
        var source = sourceArgument(args);
        var length = lengthArgument(args, 0, 14);
        return new PineSeries(function (index) {
          var total = 0;
          var weightTotal = 0;
          for (var i = 0; i < length; i += 1) {
            var value = asNumber(valueAt(source, index - i));
            if (!Number.isFinite(value)) return NaN;
            var weight = length - i;
            total += value * weight;
            weightTotal += weight;
          }
          return total / weightTotal;
        });
      },
      rsi: function (args) { return computeRsi(sourceArgument(args), lengthArgument(args, 0, 14)); },
      highest: function (args) { return rollingHighest(sourceArgument(args), lengthArgument(args, 0, 14)); },
      lowest: function (args) { return rollingLowest(sourceArgument(args), lengthArgument(args, 0, 14)); },
      sum: function (args) { return rollingSum(sourceArgument(args), lengthArgument(args, 0, 14)); },
      stdev: function (args) { return standardDeviation(sourceArgument(args), lengthArgument(args, 0, 14)); },
      change: function (args) {
        var source = sourceArgument(args);
        var offset = Math.max(1, Math.floor(asNumber(args.named.length != null ? args.named.length : args[1])) || 1);
        return new PineSeries(function (index) { return asNumber(valueAt(source, index)) - asNumber(valueAt(source, index - offset)); });
      },
      roc: function (args) {
        var source = sourceArgument(args);
        var length = lengthArgument(args, 0, 9);
        return new PineSeries(function (index) {
          var current = asNumber(valueAt(source, index));
          var previous = asNumber(valueAt(source, index - length));
          return !Number.isFinite(current) || !Number.isFinite(previous) || previous === 0 ? NaN : (current - previous) / previous * 100;
        });
      },
      crossover: function (args) {
        var left = args[0];
        var right = args[1];
        return new PineSeries(function (index) { return valueAt(left, index) > valueAt(right, index) && valueAt(left, index - 1) <= valueAt(right, index - 1); });
      },
      crossunder: function (args) {
        var left = args[0];
        var right = args[1];
        return new PineSeries(function (index) { return valueAt(left, index) < valueAt(right, index) && valueAt(left, index - 1) >= valueAt(right, index - 1); });
      },
      macd: function (args) {
        var source = args[0];
        var fast = Math.max(1, Math.floor(asNumber(args.named.fast != null ? args.named.fast : args[1])) || 12);
        var slow = Math.max(fast + 1, Math.floor(asNumber(args.named.slow != null ? args.named.slow : args[2])) || 26);
        var signalLength = Math.max(1, Math.floor(asNumber(args.named.signal != null ? args.named.signal : args[3])) || 9);
        var macd = binarySeries(rollingEma(source, fast), rollingEma(source, slow), function (a, b) { return a - b; });
        return [macd, rollingEma(macd, signalLength), binarySeries(macd, rollingEma(macd, signalLength), function (a, b) { return a - b; })];
      }
    };
    var math = {
      abs: function (args) { return mapSeries(args[0], Math.abs); },
      max: function (args) { return binarySeries(args[0], args[1], Math.max); },
      min: function (args) { return binarySeries(args[0], args[1], Math.min); },
      pow: function (args) { return binarySeries(args[0], args[1], Math.pow); },
      sqrt: function (args) { return mapSeries(args[0], function (value) { return value < 0 ? NaN : Math.sqrt(value); }); },
      log: function (args) { return mapSeries(args[0], function (value) { return value > 0 ? Math.log(value) : NaN; }); },
      log10: function (args) { return mapSeries(args[0], function (value) { return value > 0 ? Math.log10(value) : NaN; }); },
      round: function (args) { return mapSeries(args[0], Math.round); },
      floor: function (args) { return mapSeries(args[0], Math.floor); },
      ceil: function (args) { return mapSeries(args[0], Math.ceil); }
    };
    var color = { red: '#ef4444', green: '#16a34a', blue: '#2563eb', orange: '#d97706', purple: '#7c3aed', teal: '#0891b2', white: '#ffffff', black: '#111827' };
    color.new = function (args) {
      var value = String(args[0] == null ? '' : args[0]);
      var transparency = Math.max(0, Math.min(100, asNumber(args[1])));
      var match = value.match(/^#([0-9a-f]{6})$/i);
      if (!match || !Number.isFinite(transparency)) return value;
      var hex = match[1];
      var red = parseInt(hex.slice(0, 2), 16);
      var green = parseInt(hex.slice(2, 4), 16);
      var blue = parseInt(hex.slice(4, 6), 16);
      return 'rgba(' + red + ', ' + green + ', ' + blue + ', ' + (1 - transparency / 100) + ')';
    };
    color.rgb = function (args) {
      var red = Math.max(0, Math.min(255, Math.round(asNumber(args[0]))));
      var green = Math.max(0, Math.min(255, Math.round(asNumber(args[1]))));
      var blue = Math.max(0, Math.min(255, Math.round(asNumber(args[2]))));
      var transparency = args[3] == null ? 0 : Math.max(0, Math.min(100, asNumber(args[3])));
      return 'rgba(' + red + ', ' + green + ', ' + blue + ', ' + (1 - transparency / 100) + ')';
    };
    var plotStyles = {
      style_line: 'line', style_histogram: 'histogram', style_columns: 'histogram', style_area: 'area',
      style_circles: 'line', style_cross: 'line'
    };
    var plotNamespace = { style_line: 'line', style_histogram: 'histogram', style_columns: 'histogram', style_area: 'area', style_circles: 'line', style_cross: 'line' };
    return { ta: ta, math: math, color: color, plot: plotNamespace, plotStyles: plotStyles };
  }

  function baseSeries(bars, field) {
    return new PineSeries(function (index) {
      var bar = bars[index];
      return bar && Number.isFinite(Number(bar[field])) ? Number(bar[field]) : NaN;
    }, field);
  }

  function seriesEnvironment(bars) {
    var environment = {
      open: baseSeries(bars, 'open'),
      high: baseSeries(bars, 'high'),
      low: baseSeries(bars, 'low'),
      close: baseSeries(bars, 'close'),
      volume: baseSeries(bars, 'volume'),
      bar_index: new PineSeries(function (index) { return index; }, 'bar_index'),
      time: new PineSeries(function (index) { return securityTime(bars[index], index); }, 'time')
    };
    environment.hl2 = binarySeries(environment.high, environment.low, function (high, low) { return (high + low) / 2; });
    environment.hlc3 = new PineSeries(function (index) {
      return (environment.high.get(index) + environment.low.get(index) + environment.close.get(index)) / 3;
    }, 'hlc3');
    environment.ohlc4 = new PineSeries(function (index) {
      return (environment.open.get(index) + environment.high.get(index) + environment.low.get(index) + environment.close.get(index)) / 4;
    }, 'ohlc4');
    return environment;
  }

  function securityTime(bar, index) {
    var time = bar && Number(bar.time);
    return Number.isFinite(time) ? time : index;
  }

  function securityPeriod(timeframe) {
    var normalized = String(timeframe == null ? '' : timeframe).trim().toUpperCase();
    if (normalized === 'D' || normalized === '1D' || normalized === 'DAY' || normalized === 'DAILY') return 'daily';
    if (normalized === 'W' || normalized === '1W' || normalized === 'WEEK' || normalized === 'WEEKLY') return 'weekly';
    if (normalized === 'M' || normalized === '1M' || normalized === 'MONTH' || normalized === 'MONTHLY') return 'monthly';
    if (normalized === 'Q' || normalized === '1Q' || normalized === '3M' || normalized === 'QUARTERLY') return 'quarterly';
    if (normalized === 'Y' || normalized === '1Y' || normalized === 'YEAR' || normalized === 'YEARLY') return 'yearly';
    return null;
  }

  function aggregateSecurityBars(bars, timeframe) {
    var period = securityPeriod(timeframe);
    if (!period || period === 'daily' || !bars.length) return bars;
    var groups = [];
    var groupMap = Object.create(null);
    bars.forEach(function (bar, index) {
      var date = new Date(securityTime(bar, index) * 1000);
      var year = date.getUTCFullYear();
      var month = date.getUTCMonth();
      var day = date.getUTCDate();
      var key;
      if (period === 'weekly') {
        var mondayOffset = (date.getUTCDay() + 6) % 7;
        var monday = new Date(Date.UTC(year, month, day - mondayOffset));
        key = 'w:' + monday.getUTCFullYear() + '-' + monday.getUTCMonth() + '-' + monday.getUTCDate();
      } else if (period === 'monthly') key = 'm:' + year + '-' + month;
      else if (period === 'quarterly') key = 'q:' + year + '-' + Math.floor(month / 3);
      else key = 'y:' + year;
      var group = groupMap[key];
      if (!group) {
        group = { time: bar.time, open: Number(bar.open), high: Number(bar.high), low: Number(bar.low), close: Number(bar.close), volume: Number(bar.volume) || 0 };
        groupMap[key] = group;
        groups.push(group);
      } else {
        group.high = Math.max(group.high, Number(bar.high));
        group.low = Math.min(group.low, Number(bar.low));
        group.close = Number(bar.close);
        group.volume += Number(bar.volume) || 0;
        group.time = bar.time;
      }
    });
    return groups;
  }

  function alignSecuritySeries(source, sourceBars, targetBars) {
    var times = sourceBars.map(securityTime);
    return new PineSeries(function (targetIndex) {
      var targetTime = securityTime(targetBars[targetIndex], targetIndex);
      var low = 0;
      var high = times.length - 1;
      var match = -1;
      while (low <= high) {
        var middle = Math.floor((low + high) / 2);
        if (times[middle] <= targetTime) {
          match = middle;
          low = middle + 1;
        } else {
          high = middle - 1;
        }
      }
      return match < 0 ? NaN : valueAt(source, match);
    }, source && source.label ? source.label + ':aligned' : 'security');
  }

  function seriesToPoints(series, bars) {
    var points = [];
    for (var i = 0; i < bars.length; i += 1) {
      var value = valueAt(series, i);
      if (Number.isFinite(Number(value))) points.push({ time: bars[i].time, value: Number(value) });
    }
    return points;
  }

  function colorSeriesToPoints(series, bars) {
    var points = [];
    for (var i = 0; i < bars.length; i += 1) {
      var value = valueAt(series, i);
      if (typeof value === 'string' && value) points.push({ time: bars[i].time, value: 1, color: value });
    }
    return points;
  }

  function compile(source) {
    source = String(source == null ? '' : source);
    if (Object.prototype.hasOwnProperty.call(compileCache, source)) return compileCache[source];
    var compiled = { source: source, statements: parse(source) };
    compileCache[source] = compiled;
    compileCacheKeys.push(source);
    while (compileCacheKeys.length > COMPILE_CACHE_LIMIT) {
      delete compileCache[compileCacheKeys.shift()];
    }
    return compiled;
  }

  function run(compiledOrSource, bars, options) {
    options = options || {};
    bars = Array.isArray(bars) ? bars : [];
    var compiled = typeof compiledOrSource === 'string' ? compile(compiledOrSource) : compiledOrSource;
    activeBudget = { used: 0, max: MAX_OPERATIONS };
    var namespaces = createNamespaces();
    var inputDefinitions = [];
    var suppliedInputs = options.inputs || {};
    function registerInput(type, args, fallback) {
      var title = String(args.named.title != null ? args.named.title : (args[1] != null ? args[1] : 'Input ' + (inputDefinitions.length + 1)));
      var id = String(args.named.id != null ? args.named.id : title).toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '') || 'input_' + (inputDefinitions.length + 1);
      var defaultValue = args.named.defval != null ? args.named.defval : (args[0] != null ? args[0] : fallback);
      var value = Object.prototype.hasOwnProperty.call(suppliedInputs, id) ? suppliedInputs[id] : defaultValue;
      if (type === 'int') value = Math.floor(asNumber(value));
      if (type === 'float') value = asNumber(value);
      if (type === 'bool') value = !!value;
      if (type === 'string') value = String(value == null ? '' : value);
      if (type === 'source') {
        value = String(value || 'close').toLowerCase();
        if (['open', 'high', 'low', 'close', 'volume', 'hl2', 'hlc3', 'ohlc4'].indexOf(value) === -1) value = 'close';
      }
      inputDefinitions.push({ id: id, type: type, title: title, defaultValue: defaultValue, value: value, min: args.named.min, max: args.named.max, step: args.named.step });
      if (type === 'source') return env[value] || env.close;
      return value;
    }
    namespaces.input = {
      int: function (args) { return registerInput('int', args, 0); },
      float: function (args) { return registerInput('float', args, 0); },
      bool: function (args) { return registerInput('bool', args, false); },
      string: function (args) { return registerInput('string', args, ''); },
      source: function (args) { return registerInput('source', args, 'close'); },
      color: function (args) { return registerInput('color', args, '#2563eb'); }
    };
    var env = {
      open: baseSeries(bars, 'open'), high: baseSeries(bars, 'high'), low: baseSeries(bars, 'low'),
      close: baseSeries(bars, 'close'), volume: baseSeries(bars, 'volume'), bar_index: null, time: null, hl2: null, hlc3: null, ohlc4: null,
      na: NaN, true: true, false: false, ta: namespaces.ta, math: namespaces.math, color: namespaces.color,
      plot: namespaces.plot, input: namespaces.input, display: { all: 'all', none: 'none' }
    };
    env.hl2 = binarySeries(env.high, env.low, function (high, low) { return (high + low) / 2; });
    env.bar_index = new PineSeries(function (index) { return index; }, 'bar_index');
    env.time = new PineSeries(function (index) { return securityTime(bars[index], index); }, 'time');
    env.hlc3 = new PineSeries(function (index) { return (env.high.get(index) + env.low.get(index) + env.close.get(index)) / 3; });
    env.ohlc4 = new PineSeries(function (index) { return (env.open.get(index) + env.high.get(index) + env.low.get(index) + env.close.get(index)) / 4; });
    var metadata = { title: options.title || 'Pine Script', overlay: false, shorttitle: null };
    var plots = [];
    var shapePlots = [];
    var fills = [];
    var backgrounds = [];
    var pineDrawings = [];
    var levels = [];
    var alertConditions = [];
    var warnings = [];
    var securityRequests = [];
    var securityRequestKeys = Object.create(null);
    var securitySeriesCache = Object.create(null);
    var securityData = options.security || {};

    function securityEntry(symbol) {
      var keys = Object.keys(securityData);
      var normalized = String(symbol || '').trim().toUpperCase();
      for (var keyIndex = 0; keyIndex < keys.length; keyIndex += 1) {
        if (String(keys[keyIndex]).trim().toUpperCase() === normalized) return securityData[keys[keyIndex]];
      }
      return null;
    }

    function securityBars(entry) {
      if (Array.isArray(entry)) return entry;
      if (entry && Array.isArray(entry.bars)) return entry.bars;
      return [];
    }

    function rememberSecurityRequest(symbol, timeframe) {
      var normalizedSymbol = String(symbol || '').trim().toUpperCase();
      var normalizedTimeframe = String(timeframe == null ? '' : timeframe).trim() || 'chart';
      if (!normalizedSymbol) return;
      var key = normalizedSymbol + '|' + normalizedTimeframe;
      if (securityRequestKeys[key]) return;
      if (securityRequests.length >= MAX_SECURITY_REQUESTS) {
        throw new PineError('The script exceeded the maximum of ' + MAX_SECURITY_REQUESTS + ' security requests.', 1, 1);
      }
      securityRequestKeys[key] = true;
      securityRequests.push({ symbol: normalizedSymbol, timeframe: normalizedTimeframe });
    }

    function securityExpressionKey(symbol, timeframe, expression) {
      var expressionText;
      try { expressionText = JSON.stringify(expression); } catch (error) { expressionText = String(expression && expression.type || 'expression'); }
      return String(symbol) + '|' + String(timeframe) + '|' + expressionText;
    }

    function requestSecurity(args, requestEnv, index) {
      var symbolValue = args[0] ? evaluate(args[0].value, requestEnv, index) : '';
      var timeframeValue = args[1] ? evaluate(args[1].value, requestEnv, index) : 'chart';
      var symbol = String(valueAt(symbolValue, index) == null ? '' : valueAt(symbolValue, index)).trim().toUpperCase();
      var timeframe = String(valueAt(timeframeValue, index) == null ? 'chart' : valueAt(timeframeValue, index)).trim() || 'chart';
      var expressionArgument = args[2] || args.filter(function (arg) { return arg.name === 'expression'; })[0];
      if (!expressionArgument) {
        throw new PineError('request.security() requires an expression argument.', 1, 1);
      }
      var key = securityExpressionKey(symbol, timeframe, expressionArgument.value);
      if (securitySeriesCache[key]) return securitySeriesCache[key];
      var entry = securityEntry(symbol);
      var sourceBars = aggregateSecurityBars(securityBars(entry), timeframe);
      if (!sourceBars.length) {
        rememberSecurityRequest(symbol, timeframe);
        securitySeriesCache[key] = new PineSeries(function () { return NaN; }, 'security:' + symbol);
        return securitySeriesCache[key];
      }
      var securityEnv = Object.create(env);
      var securityFields = seriesEnvironment(sourceBars);
      Object.keys(securityFields).forEach(function (field) {
        securityEnv[field] = securityFields[field];
      });
      var expression = evaluate(expressionArgument.value, securityEnv, 0);
      var sourceSeries = expression instanceof PineSeries ? expression : new PineSeries(function () { return expression; }, 'security:' + symbol);
      securitySeriesCache[key] = alignSecuritySeries(sourceSeries, sourceBars, bars);
      return securitySeriesCache[key];
    }

    env.request = { security: function () { return NaN; } };
    env.__requestSecurity = requestSecurity;
    function named(args, key, fallback) { return args.named[key] == null ? fallback : args.named[key]; }
    function plotFunction(args) {
      if (plots.length >= MAX_PLOTS) throw new PineError('The script exceeded the maximum of ' + MAX_PLOTS + ' plots.');
      var source = args[0];
      var title = String(named(args, 'title', 'Plot ' + (plots.length + 1)) || 'Plot');
      var color = named(args, 'color', null);
      var width = Math.max(1, Math.min(8, Math.floor(asNumber(named(args, 'linewidth', 2))) || 2));
      var style = named(args, 'style', 'line');
      if (typeof style === 'string' && style.indexOf('style_') === 0) style = namespaces.plotStyles[style] || 'line';
      plots.push({ title: title, source: source, color: color, lineWidth: width, type: style === 'histogram' ? 'histogram' : 'line', opacity: named(args, 'transp', null) == null ? 1 : Math.max(0.05, 1 - Number(named(args, 'transp', 0)) / 100) });
      return source;
    }
    function hlineFunction(args) {
      var value = asNumber(args[0]);
      if (Number.isFinite(value)) levels.push({ value: value, title: String(named(args, 'title', 'Level')), color: named(args, 'color', null) });
      return value;
    }
    function shapePlotFunction(args, characterMode) {
      if (plots.length + shapePlots.length >= MAX_PLOTS) throw new PineError('The script exceeded the maximum of ' + MAX_PLOTS + ' plots.');
      var source = args[0];
      var output = (characterMode ? 'char' : 'shape') + (shapePlots.length + 1);
      shapePlots.push({
        output: output,
        source: source,
        title: String(named(args, 'title', characterMode ? 'Plot char' : 'Plot shape') || (characterMode ? 'Plot char' : 'Plot shape')),
        color: named(args, 'color', null),
        textColor: named(args, 'textcolor', null),
        style: named(args, 'style', characterMode ? 'char' : 'circle'),
        location: named(args, 'location', 'abovebar'),
        text: named(args, 'text', characterMode ? '' : ''),
        character: named(args, 'char', characterMode ? '•' : ''),
        size: named(args, 'size', 'normal'),
        opacity: named(args, 'transp', null) == null ? 1 : Math.max(0.05, 1 - Number(named(args, 'transp', 0)) / 100),
        characterMode: !!characterMode
      });
      return source;
    }
    function backgroundFunction(args) {
      if (backgrounds.length >= MAX_PLOTS) throw new PineError('The script exceeded the maximum of ' + MAX_PLOTS + ' plots.');
      backgrounds.push({
        output: 'background' + (backgrounds.length + 1),
        source: args[0],
        title: 'Background',
        opacity: named(args, 'transp', null) == null ? 0.16 : Math.max(0.02, 1 - Number(named(args, 'transp', 0)) / 100)
      });
      return args[0];
    }
    function fillFunction(args) {
      if (fills.length >= MAX_PLOTS) throw new PineError('The script exceeded the maximum of ' + MAX_PLOTS + ' plots.');
      fills.push({
        output: 'fill' + (fills.length + 1),
        first: args[0],
        second: args[1],
        color: named(args, 'color', null),
        opacity: named(args, 'transp', null) == null ? 0.16 : Math.max(0.02, 1 - Number(named(args, 'transp', 0)) / 100),
        title: String(named(args, 'title', 'Fill') || 'Fill')
      });
      return args[0];
    }
    function alertConditionFunction(args) {
      alertConditions.push({
        condition: args[0],
        title: String(named(args, 'title', 'Alert condition') || 'Alert condition'),
        message: String(named(args, 'message', '') || '')
      });
      return args[0];
    }
    function drawingArgument(args, name, position, index, fallback) {
      var value = args.named[name] != null ? args.named[name] : args[position];
      if (value == null) return fallback;
      return valueAt(value, Math.max(0, bars.length - 1));
    }
    function labelNewFunction(args) {
      pineDrawings.push({
        type: 'label',
        x: asNumber(drawingArgument(args, 'x', 0, 0, bars.length - 1)),
        y: asNumber(drawingArgument(args, 'y', 1, 0, NaN)),
        text: String(drawingArgument(args, 'text', 2, 0, '') || ''),
        color: drawingArgument(args, 'color', 5, 0, '#2563eb'),
        textColor: drawingArgument(args, 'textcolor', 6, 0, '#ffffff'),
        style: drawingArgument(args, 'style', 4, 0, 'label_down'),
        size: drawingArgument(args, 'size', 7, 0, 'normal')
      });
      return pineDrawings[pineDrawings.length - 1];
    }
    function lineNewFunction(args) {
      pineDrawings.push({
        type: 'line',
        x1: asNumber(drawingArgument(args, 'x1', 0, 0, 0)),
        y1: asNumber(drawingArgument(args, 'y1', 1, 0, NaN)),
        x2: asNumber(drawingArgument(args, 'x2', 2, 0, bars.length - 1)),
        y2: asNumber(drawingArgument(args, 'y2', 3, 0, NaN)),
        color: drawingArgument(args, 'color', 6, 0, '#2563eb'),
        width: Math.max(1, Math.min(8, Math.floor(asNumber(drawingArgument(args, 'width', 8, 0, 2))) || 2)),
        style: drawingArgument(args, 'style', 7, 0, 'solid')
      });
      return pineDrawings[pineDrawings.length - 1];
    }
    function boxNewFunction(args) {
      pineDrawings.push({
        type: 'box',
        left: asNumber(drawingArgument(args, 'left', 0, 0, 0)),
        top: asNumber(drawingArgument(args, 'top', 1, 0, NaN)),
        right: asNumber(drawingArgument(args, 'right', 2, 0, bars.length - 1)),
        bottom: asNumber(drawingArgument(args, 'bottom', 3, 0, NaN)),
        borderColor: drawingArgument(args, 'border_color', 4, 0, '#2563eb'),
        borderWidth: Math.max(1, Math.min(8, Math.floor(asNumber(drawingArgument(args, 'border_width', 5, 0, 2))) || 2)),
        borderStyle: drawingArgument(args, 'border_style', 6, 0, 'solid'),
        background: drawingArgument(args, 'bgcolor', 7, 0, 'rgba(37, 99, 235, 0.12)'),
        text: String(drawingArgument(args, 'text', 8, 0, '') || ''),
        textColor: drawingArgument(args, 'text_color', 9, 0, '#111827')
      });
      return pineDrawings[pineDrawings.length - 1];
    }
    function naFunction(args) { return !Number.isFinite(asNumber(valueAt(args[0], 0))) ? true : false; }
    env.__plot = plotFunction;
    env.__hline = hlineFunction;
    env.__na = naFunction;
    env.plot = function (args) { return plotFunction(args); };
    env.plot.style_line = 'line';
    env.plot.style_histogram = 'histogram';
    env.plot.style_columns = 'histogram';
    env.plot.style_area = 'area';
    env.plot.style_circles = 'line';
    env.plot.style_cross = 'line';
    env.hline = hlineFunction;
    env.plotshape = function (args) { return shapePlotFunction(args, false); };
    env.plotchar = function (args) { return shapePlotFunction(args, true); };
    env.bgcolor = backgroundFunction;
    env.fill = fillFunction;
    env.alertcondition = alertConditionFunction;
    env.label = { new: labelNewFunction, delete: function () { return true; } };
    env.line = { new: lineNewFunction, delete: function () { return true; } };
    env.box = { new: boxNewFunction, delete: function () { return true; } };
    env.shape = { circle: 'circle', square: 'square', diamond: 'diamond', triangleup: 'triangleup', triangledown: 'triangledown', cross: 'cross', xcross: 'xcross' };
    env.location = { abovebar: 'abovebar', belowbar: 'belowbar', top: 'top', bottom: 'bottom', absolute: 'absolute' };
    env.size = { tiny: 'tiny', small: 'small', normal: 'normal', large: 'large', huge: 'huge' };
    env.label.style_label_down = 'label_down';
    env.label.style_label_up = 'label_up';
    env.label.style_none = 'none';
    env.line.style_solid = 'solid';
    env.line.style_dashed = 'dash';
    env.line.style_dotted = 'dot';
    env.box.style_solid = 'solid';
    env.box.style_dashed = 'dash';
    env.box.style_dotted = 'dot';
    env.xloc = { bar_index: 'bar_index', bar_time: 'bar_time' };
    env.yloc = { price: 'price', abovebar: 'abovebar', belowbar: 'belowbar' };
    env.extend = { none: 'none', left: 'left', right: 'right', both: 'both' };
    env.na = naFunction;
    var userFunctions = {};
    var functionDepth = 0;
    var seriesIdentifiers = { open: true, high: true, low: true, close: true, volume: true, bar_index: true, time: true, hl2: true, hlc3: true, ohlc4: true };
    function resolveFunctionSeries(value, barIndex) {
      if (!(value instanceof PineSeries)) return value;
      if (functionDepth >= MAX_FUNCTION_DEPTH) throw new PineError('Function call depth exceeded the maximum of ' + MAX_FUNCTION_DEPTH + '.', 1, 1);
      functionDepth += 1;
      try {
        return resolveFunctionSeries(value.get(barIndex), barIndex);
      } finally {
        functionDepth -= 1;
      }
    }
    function expressionUsesSeries(node) {
      if (!node) return false;
      if (node.type === 'identifier') return !!seriesIdentifiers[node.name];
      if (node.type === 'member') return expressionUsesSeries(node.object);
      if (node.type === 'index') return expressionUsesSeries(node.object) || expressionUsesSeries(node.index);
      if (node.type === 'call') {
        if (node.callee && node.callee.type === 'member' && node.callee.object && node.callee.object.type === 'identifier' &&
          node.callee.object.name === 'request' && node.callee.property === 'security') return true;
        return expressionUsesSeries(node.callee) || (node.args || []).some(function (arg) { return expressionUsesSeries(arg.value); });
      }
      if (node.type === 'unary') return expressionUsesSeries(node.argument);
      if (node.type === 'conditional') return expressionUsesSeries(node.test) || expressionUsesSeries(node.consequent) || expressionUsesSeries(node.alternate);
      if (node.type === 'binary') return expressionUsesSeries(node.left) || expressionUsesSeries(node.right);
      return false;
    }
    function statementsUseSeries(statements) {
      return (statements || []).some(function (statement) {
        if (statement.type === 'assignment' || statement.type === 'tuple-assignment' || statement.type === 'return') return expressionUsesSeries(statement.expression);
        if (statement.type === 'expression') return expressionUsesSeries(statement.expression);
        if (statement.type === 'if') return expressionUsesSeries(statement.test) || statementsUseSeries(statement.consequent) || statementsUseSeries(statement.alternate);
        if (statement.type === 'for') return expressionUsesSeries(statement.start) || expressionUsesSeries(statement.end) || statementsUseSeries(statement.body);
        return false;
      });
    }
    function executeStatements(statements, scope, index) {
      var lastValue = NaN;
      for (var statementIndex = 0; statementIndex < (statements || []).length; statementIndex += 1) {
        var statement = statements[statementIndex];
        if (statement.type === 'declaration') {
          var declaration = statement.expression;
          var declarationName = declaration.callee && declaration.callee.name;
          if (declaration.type !== 'call' || ['indicator', 'study', 'strategy'].indexOf(declarationName) === -1) throw new PineError('Only indicator() declarations are supported in this release.', statement.line, 1);
          var declarationArgs = argumentMap(declaration.args, scope, index);
          metadata.title = String(declarationArgs.named.title || declarationArgs[0] || metadata.title);
          metadata.shorttitle = declarationArgs.named.shorttitle || null;
          metadata.overlay = declarationArgs.named.overlay === true;
          continue;
        }
        if (statement.type === 'function') continue;
        if (statement.type === 'assignment') {
          lastValue = evaluate(statement.expression, scope, index);
          scope[statement.name] = lastValue;
          continue;
        }
        if (statement.type === 'tuple-assignment') {
          var tupleValue = evaluate(statement.expression, scope, index);
          if (!Array.isArray(tupleValue)) throw new PineError('The expression does not return a tuple.', statement.line, 1);
          statement.names.forEach(function (name, tupleIndex) { scope[name] = tupleValue[tupleIndex]; });
          lastValue = tupleValue[0];
          continue;
        }
        if (statement.type === 'if') {
          var condition = valueAt(evaluate(statement.test, scope, index), index);
          var branchResult = executeStatements(condition ? statement.consequent : statement.alternate, scope, index);
          if (branchResult.type === 'return' || branchResult.type === 'break') return branchResult;
          lastValue = branchResult.value;
          continue;
        }
        if (statement.type === 'for') {
          var start = Math.floor(asNumber(valueAt(evaluate(statement.start, scope, index), index)));
          var end = Math.floor(asNumber(valueAt(evaluate(statement.end, scope, index), index)));
          if (!Number.isFinite(start) || !Number.isFinite(end)) continue;
          var step = end >= start ? 1 : -1;
          var iterations = Math.abs(end - start) + 1;
          if (iterations > MAX_LOOPS) throw new PineError('The loop exceeded the maximum of ' + MAX_LOOPS + ' iterations.', statement.line, 1);
          var hadLoopVariable = Object.prototype.hasOwnProperty.call(scope, statement.name);
          var previousLoopValue = scope[statement.name];
          for (var loopValue = start; step > 0 ? loopValue <= end : loopValue >= end; loopValue += step) {
            scope[statement.name] = loopValue;
            var loopResult = executeStatements(statement.body, scope, index);
            if (loopResult.type === 'return') {
              if (hadLoopVariable) scope[statement.name] = previousLoopValue;
              else delete scope[statement.name];
              return loopResult;
            }
            if (loopResult.type === 'break') {
              lastValue = loopResult.value;
              break;
            }
            lastValue = loopResult.value;
          }
          if (hadLoopVariable) scope[statement.name] = previousLoopValue;
          else delete scope[statement.name];
          continue;
        }
        if (statement.type === 'return') return { type: 'return', value: statement.expression ? evaluate(statement.expression, scope, index) : NaN };
        if (statement.type === 'break') return { type: 'break', value: lastValue };
        if (statement.type === 'expression') {
          var expression = statement.expression;
          var expressionCallee = expression.callee && expression.callee.name;
          if (expression.type === 'call' && expressionCallee === 'plot') {
            lastValue = plotFunction(argumentMap(expression.args, scope, index));
          } else if (expression.type === 'call' && expressionCallee === 'hline') {
            lastValue = hlineFunction(argumentMap(expression.args, scope, index));
          } else {
            lastValue = evaluate(expression, scope, index);
            if (expressionCallee !== 'plot' && expressionCallee !== 'hline') warnings.push('Expression result is unused on line ' + statement.line + '.');
          }
        }
      }
      return { type: 'normal', value: lastValue };
    }
    compiled.statements.forEach(function (statement) {
      if (statement.type !== 'function') return;
      if (Object.prototype.hasOwnProperty.call(userFunctions, statement.name)) {
        throw new PineError('Duplicate function "' + statement.name + '".', statement.line, 1);
      }
      userFunctions[statement.name] = statement;
    });
    Object.keys(userFunctions).forEach(function (functionName) {
      var definition = userFunctions[functionName];
      if (Object.prototype.hasOwnProperty.call(env, functionName)) {
        throw new PineError('Cannot redefine runtime identifier "' + functionName + '".', definition.line, 1);
      }
      env[functionName] = function (args, ignoredEnv, callIndex) {
        var hasSeriesArgument = (args || []).some(function (value) { return value instanceof PineSeries; });
        var shouldReturnSeries = hasSeriesArgument || expressionUsesSeries(definition.expression) || statementsUseSeries(definition.body);
        function invoke(barIndex) {
          if (functionDepth >= MAX_FUNCTION_DEPTH) throw new PineError('Function call depth exceeded the maximum of ' + MAX_FUNCTION_DEPTH + '.', definition.line, 1);
          functionDepth += 1;
          var localEnv = Object.create(env);
          definition.parameters.forEach(function (parameter, index) {
            localEnv[parameter] = args && args.named && args.named[parameter] !== undefined ? args.named[parameter] : args && args[index];
          });
          try {
            if (definition.body) {
              var bodyResult = executeStatements(definition.body, localEnv, barIndex);
              return bodyResult.value;
            }
            return evaluate(definition.expression, localEnv, barIndex);
          } finally {
            functionDepth -= 1;
          }
        }
        if (!shouldReturnSeries) return invoke(callIndex);
        return new PineSeries(function (barIndex) {
          var value = invoke(barIndex);
          return resolveFunctionSeries(value, barIndex);
        }, functionName);
      };
    });
    executeStatements(compiled.statements, env, 0);
    var outputs = {};
    var render = [];
    plots.forEach(function (plot, index) {
      var output = 'plot' + (index + 1);
      outputs[output] = seriesToPoints(plot.source, bars);
      render.push({ output: output, type: plot.type, title: plot.title, color: plot.color, lineWidth: plot.lineWidth, opacity: plot.opacity });
    });
    levels.forEach(function (level, index) {
      render.push({ output: 'level' + (index + 1), type: 'level', value: level.value, title: level.title, color: level.color });
    });
    shapePlots.forEach(function (plot) {
      outputs[plot.output] = seriesToPoints(plot.source, bars);
      render.push({
        output: plot.output,
        type: plot.characterMode ? 'char' : 'shape',
        title: plot.title,
        color: plot.color,
        textColor: plot.textColor,
        style: plot.style,
        location: plot.location,
        text: plot.text,
        character: plot.character,
        size: plot.size,
        opacity: plot.opacity
      });
    });
    backgrounds.forEach(function (background) {
      outputs[background.output] = colorSeriesToPoints(background.source, bars);
      render.push({ output: background.output, type: 'background', title: background.title, opacity: background.opacity });
    });
    fills.forEach(function (fill, index) {
      var firstOutput = 'fill' + (index + 1) + '_first';
      var secondOutput = 'fill' + (index + 1) + '_second';
      outputs[firstOutput] = seriesToPoints(fill.first, bars);
      outputs[secondOutput] = seriesToPoints(fill.second, bars);
      render.push({ output: fill.output, type: 'fill', firstOutput: firstOutput, secondOutput: secondOutput, title: fill.title, color: fill.color, opacity: fill.opacity });
    });
    alertConditions.forEach(function (condition, index) {
      var output = 'alert' + (index + 1);
      outputs[output] = seriesToPoints(condition.condition, bars);
      condition.output = output;
    });
    var result = {
      metadata: metadata,
      outputs: outputs,
      render: render,
      plots: plots,
      inputs: inputDefinitions,
      warnings: warnings,
      drawings: pineDrawings,
      alertConditions: alertConditions.map(function (condition) {
        return { output: condition.output, title: condition.title, message: condition.message };
      }),
      securityRequests: securityRequests
    };
    activeBudget = null;
    return result;
  }

  function runInWorker(message) {
    try {
      var result = run(message.source, message.bars, message.options || {});
      return { ok: true, result: result };
    } catch (error) {
      return { ok: false, error: { message: error.toString(), line: error.line, column: error.column } };
    }
  }

  return {
    VERSION: VERSION,
    PineError: PineError,
    PineSeries: PineSeries,
    tokenize: tokenize,
    parse: parse,
    compile: compile,
    run: run,
    runInWorker: runInWorker,
    limits: {
      maxSourceLength: MAX_SOURCE_LENGTH,
      maxPlots: MAX_PLOTS,
      maxLoops: MAX_LOOPS,
      maxFunctionDepth: MAX_FUNCTION_DEPTH,
      maxSecurityRequests: MAX_SECURITY_REQUESTS,
      maxOperations: MAX_OPERATIONS,
      compileCacheSize: COMPILE_CACHE_LIMIT
    }
  };
}));
