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

  var VERSION = '0.4.0';
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

  Parser.prototype.parseSwitchExpression = function (line) {
    var selector = null;
    if (this.current().type !== 'newline' && this.current().type !== 'indent') selector = this.parseExpression(0);
    this.skipNewlines();
    if (this.current().type !== 'indent') throw new PineError('Expected an indented switch block.', line, 1);
    this.take();
    var cases = [];
    this.skipNewlines();
    while (this.current().type !== 'dedent' && this.current().type !== 'eof') {
      var condition = null;
      if (!this.match('=>')) condition = this.parseExpression(0);
      this.expect('=>');
      var value = this.parseExpression(0);
      cases.push({ condition: condition, value: value });
      while (this.match(';') || this.current().type === 'newline') this.take();
      this.skipNewlines();
    }
    if (this.current().type === 'dedent') this.take();
    return { type: 'switch', selector: selector, cases: cases, line: line };
  };

  Parser.prototype.parseMethodDeclaration = function () {
    var token = this.take();
    var name = this.take();
    if (!name || name.type !== 'identifier') throw new PineError('Expected a method name.', token.line, token.column);
    this.expect('(');
    var parameters = [];
    while (!this.match(')')) {
      var parameterTokens = [];
      while (!this.match(',') && !this.match(')')) parameterTokens.push(this.take());
      for (var i = parameterTokens.length - 1; i >= 0; i -= 1) {
        if (parameterTokens[i] && parameterTokens[i].type === 'identifier') { parameters.push(parameterTokens[i].value); break; }
      }
      if (this.match(',')) this.take();
    }
    this.expect(')');
    this.expect('=>');
    if (this.current().type === 'newline') return { type: 'function', name: name.value, parameters: parameters, body: this.parseIndentedBlock(), line: name.line, method: true };
    return { type: 'function', name: name.value, parameters: parameters, expression: this.parseExpression(0), line: name.line, method: true };
  };

  Parser.prototype.parseDeclarationFields = function () {
    var fields = [];
    if (this.current().type === 'newline') this.take();
    this.skipNewlines();
    if (this.current().type !== 'indent') return fields;
    this.take();
    while (this.current().type !== 'dedent' && this.current().type !== 'eof') {
      var lineTokens = [];
      while (this.current().type !== 'newline' && this.current().type !== 'eof') lineTokens.push(this.take());
      fields.push(lineTokens.filter(function (item) { return item.type === 'identifier'; }).map(function (item) { return item.value; }));
      if (this.current().type === 'newline') this.take();
      this.skipNewlines();
    }
    if (this.current().type === 'dedent') this.take();
    return fields;
  };

  Parser.prototype.parseStatement = function () {
    var token = this.current();
    if (token.type === 'identifier' && token.value === 'method') return this.parseMethodDeclaration();
    if (token.type === 'identifier' && (token.value === 'import' || token.value === 'export')) {
      this.take();
      while (this.current().type !== 'newline' && this.current().type !== 'eof') this.take();
      return { type: 'noop', line: token.line };
    }
    if (token.type === 'identifier' && (token.value === 'type' || token.value === 'enum')) {
      this.take();
      var declarationName = this.take();
      if (!declarationName || declarationName.type !== 'identifier') throw new PineError('Expected a declaration name.', token.line, token.column);
      var declarationBody = this.current().type === 'newline' ? this.parseDeclarationFields() : [];
      return { type: token.value === 'type' ? 'type-declaration' : 'enum-declaration', name: declarationName.value, body: declarationBody, line: token.line };
    }
    if (token.type === 'identifier' && ['int', 'float', 'bool', 'string', 'color'].indexOf(token.value) !== -1 && this.peek().type === 'identifier' && this.peek(2).value === '=') {
      var explicitType = this.take().value;
      var explicitName = this.take();
      this.expect('=');
      return { type: 'assignment', name: explicitName.value, explicitType: explicitType, expression: this.parseExpression(0), line: token.line };
    }
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
      if (this.current().type === 'identifier' && this.current().value === 'in') {
        this.take();
        return { type: 'for-in', name: loopVariable.value, iterable: this.parseExpression(0), body: this.parseBlockBody(token.line, 'for'), line: token.line };
      }
      this.expect('=');
      var loopStart = this.parseExpression(0);
      var rangeKeyword = this.take();
      if (!rangeKeyword || rangeKeyword.type !== 'identifier' || rangeKeyword.value !== 'to') throw new PineError('Expected "to" in for loop.', rangeKeyword && rangeKeyword.line, rangeKeyword && rangeKeyword.column);
      var loopEnd = this.parseExpression(0);
      return { type: 'for', name: loopVariable.value, start: loopStart, end: loopEnd, body: this.parseBlockBody(token.line, 'for'), line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'while') {
      this.take();
      return { type: 'while', test: this.parseExpression(0), body: this.parseBlockBody(token.line, 'while'), line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'return') {
      this.take();
      return { type: 'return', expression: this.current().type === 'newline' ? null : this.parseExpression(0), line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'break') {
      this.take();
      return { type: 'break', line: token.line };
    }
    if (token.type === 'identifier' && token.value === 'continue') {
      this.take();
      return { type: 'continue', line: token.line };
    }
    if (token.type === 'identifier' && (token.value === 'var' || token.value === 'varip' || token.value === 'const')) {
      this.take();
      if (this.current().type === 'identifier' && ['int', 'float', 'bool', 'string', 'color'].indexOf(this.current().value) !== -1) this.take();
      var varName = this.take();
      if (!varName || varName.type !== 'identifier') throw new PineError('Expected a variable name.', token.line, token.column);
      this.expect('=');
      return { type: 'assignment', name: varName.value, expression: this.parseExpression(0), persistent: true, line: token.line };
    }
    if (token.type === 'identifier' && this.peek().value === '(' && (token.value === 'indicator' || token.value === 'study' || token.value === 'strategy' || token.value === 'library')) {
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
      if (token.value === '<' && this.peek().type === 'identifier' && this.peek(2).value === '>' && this.peek(3).value === '(') {
        this.take(); this.take(); this.take();
        continue;
      }
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
      while (this.current().type === 'newline' || this.current().type === 'indent') this.take();
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
      if (token.value === 'switch') return this.parseSwitchExpression(token.line);
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
      if (indexed && indexed.__pineMap) return indexed.data[String(offset)];
      return NaN;
    }
    if (node.type === 'call') {
      if (node.callee && node.callee.type === 'member' && node.callee.object && node.callee.object.type === 'identifier' &&
        node.callee.object.name === 'request' && ['security', 'security_lower_tf'].indexOf(node.callee.property) !== -1 && typeof env.__requestSecurity === 'function') {
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
    if (node.type === 'switch') {
      var switchSelector = node.selector ? evaluate(node.selector, env, index) : null;
      var switchCases = node.cases || [];
      var selected = null;
      for (var switchIndex = 0; switchIndex < switchCases.length; switchIndex += 1) {
        var switchCase = switchCases[switchIndex];
        var matches = switchCase.condition == null ? true : (node.selector ? valueAt(evaluate(switchCase.condition, env, index), index) === valueAt(switchSelector, index) : !!valueAt(evaluate(switchCase.condition, env, index), index));
        if (matches) { selected = evaluate(switchCase.value, env, index); break; }
      }
      return selected == null ? NaN : selected;
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

  function rollingWma(source, length) {
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
      return weightTotal ? total / weightTotal : NaN;
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

  function seriesUnary(source, mapper, label) {
    return mapSeries(source, function (value, index) {
      return mapper(value, index);
    }, label);
  }

  function rollingValues(source, length, index) {
    var values = [];
    for (var i = 0; i < length; i += 1) {
      var value = asNumber(valueAt(source, index - i));
      if (!Number.isFinite(value)) return null;
      values.push(value);
    }
    return values;
  }

  function rollingMedian(source, length) {
    return new PineSeries(function (index) {
      var values = rollingValues(source, length, index);
      if (!values) return NaN;
      values.sort(function (a, b) { return a - b; });
      var middle = Math.floor(values.length / 2);
      return values.length % 2 ? values[middle] : (values[middle - 1] + values[middle]) / 2;
    });
  }

  function rollingMode(source, length) {
    return new PineSeries(function (index) {
      var values = rollingValues(source, length, index);
      if (!values) return NaN;
      var counts = Object.create(null);
      var best = values[0];
      var bestCount = 0;
      values.forEach(function (value) {
        var key = String(value);
        counts[key] = (counts[key] || 0) + 1;
        if (counts[key] > bestCount) { best = value; bestCount = counts[key]; }
      });
      return best;
    });
  }

  function rollingPercentile(source, length, percentile) {
    return new PineSeries(function (index) {
      var values = rollingValues(source, length, index);
      if (!values) return NaN;
      values.sort(function (a, b) { return a - b; });
      var rank = Math.max(0, Math.min(100, asNumber(percentile))) / 100 * (values.length - 1);
      var lower = Math.floor(rank);
      var upper = Math.ceil(rank);
      return values[lower] + (values[upper] - values[lower]) * (rank - lower);
    });
  }

  function rollingVariance(source, length) {
    return new PineSeries(function (index) {
      var values = rollingValues(source, length, index);
      if (!values) return NaN;
      var mean = values.reduce(function (sum, value) { return sum + value; }, 0) / values.length;
      return values.reduce(function (sum, value) { return sum + Math.pow(value - mean, 2); }, 0) / values.length;
    });
  }

  function rollingDeviation(source, length) {
    return new PineSeries(function (index) {
      var values = rollingValues(source, length, index);
      if (!values) return NaN;
      var mean = values.reduce(function (sum, value) { return sum + value; }, 0) / values.length;
      return values.reduce(function (sum, value) { return sum + Math.abs(value - mean); }, 0) / values.length;
    });
  }

  function rollingBars(source, length, highest) {
    return new PineSeries(function (index) {
      var values = rollingValues(source, length, index);
      if (!values) return NaN;
      var best = highest ? -Infinity : Infinity;
      var bestIndex = NaN;
      values.forEach(function (value, offset) {
        if ((highest && value > best) || (!highest && value < best)) { best = value; bestIndex = offset; }
      });
      return bestIndex;
    });
  }

  function rollingDirection(source, length, rising) {
    return new PineSeries(function (index) {
      var current = asNumber(valueAt(source, index));
      if (!Number.isFinite(current)) return false;
      for (var i = 1; i < length; i += 1) {
        var previous = asNumber(valueAt(source, index - i));
        var next = asNumber(valueAt(source, index - i + 1));
        if (!Number.isFinite(previous) || !Number.isFinite(next) || (rising ? next <= previous : next >= previous)) return false;
      }
      return true;
    });
  }

  function rollingCorrelation(left, right, length) {
    return new PineSeries(function (index) {
      var xs = rollingValues(left, length, index);
      var ys = rollingValues(right, length, index);
      if (!xs || !ys) return NaN;
      var xm = xs.reduce(function (sum, value) { return sum + value; }, 0) / length;
      var ym = ys.reduce(function (sum, value) { return sum + value; }, 0) / length;
      var numerator = 0;
      var xsum = 0;
      var ysum = 0;
      for (var i = 0; i < length; i += 1) {
        var xd = xs[i] - xm;
        var yd = ys[i] - ym;
        numerator += xd * yd;
        xsum += xd * xd;
        ysum += yd * yd;
      }
      return xsum && ysum ? numerator / Math.sqrt(xsum * ysum) : NaN;
    });
  }

  function rollingLinreg(source, length, offset) {
    return new PineSeries(function (index) {
      var values = rollingValues(source, length, index);
      if (!values) return NaN;
      var xMean = (length - 1) / 2;
      var yMean = values.reduce(function (sum, value) { return sum + value; }, 0) / length;
      var numerator = 0;
      var denominator = 0;
      for (var i = 0; i < length; i += 1) {
        numerator += (i - xMean) * (values[i] - yMean);
        denominator += Math.pow(i - xMean, 2);
      }
      var slope = denominator ? numerator / denominator : 0;
      var intercept = yMean - slope * xMean;
      return intercept + slope * (length - 1 - (Number(offset) || 0));
    });
  }

  function trueRangeSeries(high, low, close, handleNa) {
    return new PineSeries(function (index) {
      var h = asNumber(high.get(index));
      var l = asNumber(low.get(index));
      var previous = asNumber(close.get(index - 1));
      if (!Number.isFinite(h) || !Number.isFinite(l)) return NaN;
      if (!Number.isFinite(previous)) return handleNa ? h - l : NaN;
      return Math.max(h - l, Math.abs(h - previous), Math.abs(l - previous));
    }, 'tr');
  }

  function stochasticSeries(source, high, low, length) {
    return new PineSeries(function (index) {
      var current = asNumber(valueAt(source, index));
      var highs = rollingValues(high, length, index);
      var lows = rollingValues(low, length, index);
      if (!Number.isFinite(current) || !highs || !lows) return NaN;
      var highest = Math.max.apply(Math, highs);
      var lowest = Math.min.apply(Math, lows);
      return highest === lowest ? 0 : (current - lowest) / (highest - lowest) * 100;
    });
  }

  function createNamespaces() {
    var ta = {
      sma: function (args) { return rollingSma(sourceArgument(args), lengthArgument(args, 0, 14)); },
      ema: function (args) { return rollingEma(sourceArgument(args), lengthArgument(args, 0, 14)); },
      rma: function (args) { return rollingRma(sourceArgument(args), lengthArgument(args, 0, 14)); },
      wma: function (args) { return rollingWma(sourceArgument(args), lengthArgument(args, 0, 14)); },
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
      },
      vwma: function (args) {
        var source = sourceArgument(args);
        var volume = args.named.volume != null ? args.named.volume : args[2];
        var length = lengthArgument(args, 0, 14);
        if (!(volume instanceof PineSeries)) volume = volume || source;
        var weighted = binarySeries(source, volume, function (a, b) { return a * b; });
        return binarySeries(rollingSma(weighted, length), rollingSma(volume, length), function (a, b) { return b === 0 ? NaN : a / b; });
      },
      hma: function (args) {
        var source = sourceArgument(args);
        var length = lengthArgument(args, 0, 14);
        var half = Math.max(1, Math.floor(length / 2));
        var root = Math.max(1, Math.floor(Math.sqrt(length)));
        return rollingWma(binarySeries(rollingWma(source, half), rollingWma(source, length), function (a, b) { return 2 * a - b; }), root);
      },
      swma: function (args) {
        var source = sourceArgument(args);
        return new PineSeries(function (index) {
          var values = [valueAt(source, index), valueAt(source, index - 1), valueAt(source, index - 2), valueAt(source, index - 3)].map(asNumber);
          if (values.some(function (value) { return !Number.isFinite(value); })) return NaN;
          return (values[0] + 2 * values[1] + 2 * values[2] + values[3]) / 6;
        });
      },
      mom: function (args) {
        var source = sourceArgument(args);
        var length = lengthArgument(args, 0, 10);
        return new PineSeries(function (index) {
          var current = asNumber(valueAt(source, index));
          var previous = asNumber(valueAt(source, index - length));
          return Number.isFinite(current) && Number.isFinite(previous) ? current - previous : NaN;
        });
      },
      cci: function (args) {
        var source = sourceArgument(args);
        var length = lengthArgument(args, 0, 20);
        return new PineSeries(function (index) {
          var values = rollingValues(source, length, index);
          if (!values) return NaN;
          var mean = values.reduce(function (sum, value) { return sum + value; }, 0) / length;
          var deviation = values.reduce(function (sum, value) { return sum + Math.abs(value - mean); }, 0) / length;
          return deviation === 0 ? 0 : (values[0] - mean) / (0.015 * deviation);
        });
      },
      dev: function (args) { return rollingDeviation(sourceArgument(args), lengthArgument(args, 0, 10)); },
      variance: function (args) { return rollingVariance(sourceArgument(args), lengthArgument(args, 0, 10)); },
      median: function (args) { return rollingMedian(sourceArgument(args), lengthArgument(args, 0, 10)); },
      mode: function (args) { return rollingMode(sourceArgument(args), lengthArgument(args, 0, 10)); },
      highestbars: function (args) { return rollingBars(sourceArgument(args), lengthArgument(args, 0, 14), true); },
      lowestbars: function (args) { return rollingBars(sourceArgument(args), lengthArgument(args, 0, 14), false); },
      rising: function (args) { return rollingDirection(sourceArgument(args), lengthArgument(args, 0, 2), true); },
      falling: function (args) { return rollingDirection(sourceArgument(args), lengthArgument(args, 0, 2), false); },
      correlation: function (args) { return rollingCorrelation(args[0], args[1], lengthArgument(args, 0, 20)); },
      covariance: function (args) {
        var length = lengthArgument(args, 0, 20);
        var left = args[0];
        var right = args[1];
        return new PineSeries(function (index) {
          var xs = rollingValues(left, length, index);
          var ys = rollingValues(right, length, index);
          if (!xs || !ys) return NaN;
          var xm = xs.reduce(function (sum, value) { return sum + value; }, 0) / length;
          var ym = ys.reduce(function (sum, value) { return sum + value; }, 0) / length;
          return xs.reduce(function (sum, value, offset) { return sum + (value - xm) * (ys[offset] - ym); }, 0) / length;
        });
      },
      linreg: function (args) { return rollingLinreg(sourceArgument(args), lengthArgument(args, 0, 14), args.named.offset != null ? args.named.offset : args[2]); },
      slope: function (args) {
        var source = sourceArgument(args);
        var length = lengthArgument(args, 0, 14);
        return new PineSeries(function (index) {
          var values = rollingValues(source, length, index);
          if (!values) return NaN;
          var xMean = (length - 1) / 2;
          var yMean = values.reduce(function (sum, value) { return sum + value; }, 0) / length;
          var numerator = 0;
          var denominator = 0;
          for (var i = 0; i < length; i += 1) { numerator += (i - xMean) * (values[i] - yMean); denominator += Math.pow(i - xMean, 2); }
          return denominator ? numerator / denominator : 0;
        });
      },
      valuewhen: function (args) {
        var condition = args[0];
        var source = args[1];
        var occurrence = Math.max(0, Math.floor(asNumber(args[2])) || 0);
        return new PineSeries(function (index) {
          var found = 0;
          for (var i = index; i >= 0; i -= 1) if (valueAt(condition, i)) { if (found++ === occurrence) return valueAt(source, i); }
          return NaN;
        });
      },
      barssince: function (args) {
        var condition = args[0];
        return new PineSeries(function (index) {
          for (var i = 0; i <= index; i += 1) if (valueAt(condition, index - i)) return i;
          return NaN;
        });
      },
      cross: function (args) {
        var left = args[0];
        var right = args[1];
        return new PineSeries(function (index) {
          var currentLeft = asNumber(valueAt(left, index));
          var currentRight = asNumber(valueAt(right, index));
          var previousLeft = asNumber(valueAt(left, index - 1));
          var previousRight = asNumber(valueAt(right, index - 1));
          return Number.isFinite(currentLeft) && Number.isFinite(currentRight) && Number.isFinite(previousLeft) && Number.isFinite(previousRight) && ((currentLeft > currentRight && previousLeft <= previousRight) || (currentLeft < currentRight && previousLeft >= previousRight));
        });
      },
      range: function (args) {
        var length = lengthArgument(args, 0, 14);
        return binarySeries(rollingHighest(sourceArgument(args), length), rollingLowest(sourceArgument(args), length), function (a, b) { return a - b; });
      },
      percentile_linear_interpolation: function (args) { return rollingPercentile(sourceArgument(args), lengthArgument(args, 0, 10), args.named.percentile != null ? args.named.percentile : args[2]); },
      percentile_nearest_rank: function (args) {
        var source = sourceArgument(args);
        var length = lengthArgument(args, 0, 10);
        var percentile = args.named.percentile != null ? args.named.percentile : args[2];
        return new PineSeries(function (index) {
          var values = rollingValues(source, length, index);
          if (!values) return NaN;
          values.sort(function (a, b) { return a - b; });
          var rank = Math.max(1, Math.ceil(Math.max(0, Math.min(100, asNumber(percentile))) / 100 * values.length));
          return values[Math.min(values.length, rank) - 1];
        });
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
    var color = { red: '#ef4444', green: '#16a34a', blue: '#2563eb', orange: '#d97706', purple: '#7c3aed', teal: '#0891b2', aqua: '#00bcd4', yellow: '#facc15', fuchsia: '#d946ef', lime: '#84cc16', maroon: '#991b1b', navy: '#1e3a8a', white: '#ffffff', black: '#111827', gray: '#6b7280' };
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
    color.from_gradient = function (args) {
      var value = asNumber(args[0]);
      var bottom = asNumber(args[1]);
      var top = asNumber(args[2]);
      var bottomColor = String(args[3] || '#000000');
      var topColor = String(args[4] || '#ffffff');
      var bottomMatch = bottomColor.match(/^#([0-9a-f]{6})$/i);
      var topMatch = topColor.match(/^#([0-9a-f]{6})$/i);
      if (!bottomMatch || !topMatch || !Number.isFinite(value) || top === bottom) return bottomColor;
      var ratio = Math.max(0, Math.min(1, (value - bottom) / (top - bottom)));
      var channels = [0, 1, 2].map(function (offset) {
        return Math.round(parseInt(bottomMatch[1].slice(offset * 2, offset * 2 + 2), 16) * (1 - ratio) + parseInt(topMatch[1].slice(offset * 2, offset * 2 + 2), 16) * ratio);
      });
      return 'rgb(' + channels.join(', ') + ')';
    };
    function colorChannel(value, channel) {
      var text = String(value || '');
      var match = text.match(/^#([0-9a-f]{6})$/i);
      if (match) return parseInt(match[1].slice(channel * 2, channel * 2 + 2), 16);
      var rgb = text.match(/rgba?\s*\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)/i);
      return rgb ? Number(rgb[channel + 1]) : NaN;
    }
    color.r = function (args) { return colorChannel(args[0], 0); };
    color.g = function (args) { return colorChannel(args[0], 1); };
    color.b = function (args) { return colorChannel(args[0], 2); };
    color.t = function (args) {
      var value = String(args[0] || '');
      var rgba = value.match(/rgba?\s*\([^,]+,[^,]+,[^,]+,\s*([\d.]+)\s*\)/i);
      return rgba ? (1 - Number(rgba[1])) * 100 : 0;
    };
    function stringSeries(value, mapper, label) {
      return value instanceof PineSeries ? new PineSeries(function (index) { return mapper(String(valueAt(value, index)), index); }, label) : mapper(String(value == null ? '' : value), 0);
    }
    var str = {
      contains: function (args) { return stringSeries(args[0], function (value) { return value.indexOf(String(args[1] == null ? '' : args[1])) !== -1; }); },
      endswith: function (args) { return stringSeries(args[0], function (value) { return value.endsWith(String(args[1] == null ? '' : args[1])); }); },
      startswith: function (args) { return stringSeries(args[0], function (value) { return value.startsWith(String(args[1] == null ? '' : args[1])); }); },
      length: function (args) { return stringSeries(args[0], function (value) { return value.length; }); },
      lower: function (args) { return stringSeries(args[0], function (value) { return value.toLowerCase(); }); },
      upper: function (args) { return stringSeries(args[0], function (value) { return value.toUpperCase(); }); },
      replace: function (args) { return stringSeries(args[0], function (value) { var from = String(args[1] == null ? '' : args[1]); return value.replace(from, String(args[2] == null ? '' : args[2])); }); },
      replace_all: function (args) { return stringSeries(args[0], function (value) { var from = String(args[1] == null ? '' : args[1]); return from ? value.split(from).join(String(args[2] == null ? '' : args[2])) : value; }); },
      substring: function (args) { return stringSeries(args[0], function (value) { return value.substring(Math.max(0, Math.floor(asNumber(args[1])) || 0), args[2] == null ? value.length : Math.max(0, Math.floor(asNumber(args[2])) || 0)); }); },
      split: function (args) { return String(args[0] == null ? '' : args[0]).split(String(args[1] == null ? '' : args[1])); },
      format: function (args) { var template = String(args[0] == null ? '' : args[0]); return template.replace(/\{(\d+)\}/g, function (match, index) { return args[Number(index) + 1] == null ? '' : String(args[Number(index) + 1]); }); },
      format_time: function (args) { var date = new Date(Number(args[1])); return Number.isFinite(date.getTime()) ? date.toISOString() : ''; },
      tonumber: function (args) { return stringSeries(args[0], function (value) { var parsed = Number(value); return Number.isFinite(parsed) ? parsed : NaN; }); },
      tostring: function (args) { return stringSeries(args[0], function (value) { return value; }); },
      trim: function (args) { return stringSeries(args[0], function (value) { return value.trim(); }); },
      pos: function (args) { return stringSeries(args[0], function (value) { return value.indexOf(String(args[1] == null ? '' : args[1])); }); },
      match: function (args) { return stringSeries(args[0], function (value) { var match = value.match(new RegExp(String(args[1] || ''))); return match ? match[0] : ''; }); }
    };
    function arrayValues(value) { return Array.isArray(value) ? value : []; }
    function numericArray(value) { return arrayValues(value).map(asNumber).filter(Number.isFinite); }
    var array = {
      new: function (args) { var size = Math.max(0, Math.floor(asNumber(args[0])) || 0); var initial = args[1]; return Array.apply(null, Array(size)).map(function () { return initial; }); },
      from: function (args) { return Array.prototype.slice.call(args); },
      size: function (args) { return arrayValues(args[0]).length; },
      get: function (args) { return arrayValues(args[0])[Math.max(0, Math.floor(asNumber(args[1])) || 0)]; },
      set: function (args) { if (Array.isArray(args[0])) args[0][Math.max(0, Math.floor(asNumber(args[1])) || 0)] = args[2]; return args[0]; },
      push: function (args) { if (Array.isArray(args[0])) args[0].push(args[1]); return args[0]; },
      pop: function (args) { return Array.isArray(args[0]) ? args[0].pop() : NaN; },
      shift: function (args) { return Array.isArray(args[0]) ? args[0].shift() : NaN; },
      unshift: function (args) { if (Array.isArray(args[0])) args[0].unshift(args[1]); return args[0]; },
      insert: function (args) { if (Array.isArray(args[0])) args[0].splice(Math.max(0, Math.floor(asNumber(args[1])) || 0), 0, args[2]); return args[0]; },
      remove: function (args) { return Array.isArray(args[0]) ? args[0].splice(Math.max(0, Math.floor(asNumber(args[1])) || 0), 1)[0] : NaN; },
      clear: function (args) { if (Array.isArray(args[0])) args[0].length = 0; return args[0]; },
      copy: function (args) { return arrayValues(args[0]).slice(); },
      sort: function (args) { if (Array.isArray(args[0])) args[0].sort(function (a, b) { return asNumber(a) - asNumber(b); }); return args[0]; },
      reverse: function (args) { if (Array.isArray(args[0])) args[0].reverse(); return args[0]; },
      slice: function (args) { return arrayValues(args[0]).slice(Math.max(0, Math.floor(asNumber(args[1])) || 0), args[2] == null ? undefined : Math.max(0, Math.floor(asNumber(args[2])) || 0)); },
      concat: function (args) { return arrayValues(args[0]).concat(arrayValues(args[1])); },
      binary_search: function (args) { var values = arrayValues(args[0]); var target = args[1]; return values.indexOf(target); },
      binary_search_leftmost: function (args) { return arrayValues(args[0]).findIndex(function (value) { return value >= args[1]; }); },
      binary_search_rightmost: function (args) { var values = arrayValues(args[0]); for (var i = values.length - 1; i >= 0; i -= 1) if (values[i] <= args[1]) return i; return -1; },
      range: function (args) { var start = Math.floor(asNumber(args[0])) || 0; var end = Math.floor(asNumber(args[1])) || 0; var step = args[2] == null ? 1 : Math.floor(asNumber(args[2])) || 1; var result = []; for (var value = start; step > 0 ? value < end : value > end; value += step) result.push(value); return result; },
      avg: function (args) { var values = numericArray(args[0]); return values.length ? values.reduce(function (a, b) { return a + b; }, 0) / values.length : NaN; },
      covariance: function (args) { var left = numericArray(args[0]); var right = numericArray(args[1]); var length = Math.min(left.length, right.length); if (!length) return NaN; var lm = left.slice(0, length).reduce(function (a, b) { return a + b; }, 0) / length; var rm = right.slice(0, length).reduce(function (a, b) { return a + b; }, 0) / length; return left.slice(0, length).reduce(function (sum, value, index) { return sum + (value - lm) * (right[index] - rm); }, 0) / length; },
      max: function (args) { return numericArray(args[0]).reduce(function (a, b) { return Math.max(a, b); }, -Infinity); },
      min: function (args) { return numericArray(args[0]).reduce(function (a, b) { return Math.min(a, b); }, Infinity); },
      median: function (args) { var values = numericArray(args[0]).sort(function (a, b) { return a - b; }); if (!values.length) return NaN; var middle = Math.floor(values.length / 2); return values.length % 2 ? values[middle] : (values[middle - 1] + values[middle]) / 2; },
      mode: function (args) {
        var values = arrayValues(args[0]);
        var counts = Object.create(null);
        var best = values[0];
        var bestCount = 0;
        values.forEach(function (value) {
          var key = String(value);
          counts[key] = (counts[key] || 0) + 1;
          if (counts[key] > bestCount) {
            best = value;
            bestCount = counts[key];
          }
        });
        return best;
      },
      percentile_linear_interpolation: function (args) { return rollingPercentile(arrayValues(args[0]), Math.max(1, arrayValues(args[0]).length), args[1]).get(0); },
      percentile_nearest_rank: function (args) { var values = numericArray(args[0]).sort(function (a, b) { return a - b; }); return values.length ? values[Math.min(values.length, Math.max(1, Math.ceil(values.length * asNumber(args[1]) / 100))) - 1] : NaN; },
      stdev: function (args) { var values = numericArray(args[0]); if (!values.length) return NaN; var mean = values.reduce(function (a, b) { return a + b; }, 0) / values.length; return Math.sqrt(values.reduce(function (sum, value) { return sum + Math.pow(value - mean, 2); }, 0) / values.length); },
      standardize: function (args) { var values = numericArray(args[0]); var mean = array.avg([values]); var deviation = array.stdev([values]); return values.map(function (value) { return deviation ? (value - mean) / deviation : 0; }); },
      sum: function (args) { return numericArray(args[0]).reduce(function (a, b) { return a + b; }, 0); },
      join: function (args) { return arrayValues(args[0]).join(String(args[1] == null ? ',' : args[1])); }
    };
    ['float', 'int', 'bool', 'string', 'color'].forEach(function (type) { array['new_' + type] = array.new; });
    function matrixShape(value) { return Array.isArray(value) ? { rows: value.length, columns: value.length && Array.isArray(value[0]) ? value[0].length : 0 } : { rows: 0, columns: 0 }; }
    var matrix = {
      new: function (args) { var rows = Math.max(0, Math.floor(asNumber(args[0])) || 0); var columns = Math.max(0, Math.floor(asNumber(args[1])) || 0); var initial = args[2]; return Array.apply(null, Array(rows)).map(function () { return Array.apply(null, Array(columns)).map(function () { return initial; }); }); },
      rows: function (args) { return matrixShape(args[0]).rows; },
      columns: function (args) { return matrixShape(args[0]).columns; },
      get: function (args) { return args[0] && args[0][Math.floor(asNumber(args[1]))] ? args[0][Math.floor(asNumber(args[1]))][Math.floor(asNumber(args[2]))] : NaN; },
      set: function (args) { if (args[0] && args[0][Math.floor(asNumber(args[1]))]) args[0][Math.floor(asNumber(args[1]))][Math.floor(asNumber(args[2]))] = args[3]; return args[0]; },
      add: function (args) { return matrixBinary(args[0], args[1], function (a, b) { return asNumber(a) + asNumber(b); }); },
      sub: function (args) { return matrixBinary(args[0], args[1], function (a, b) { return asNumber(a) - asNumber(b); }); },
      mult: function (args) { return matrixBinary(args[0], args[1], function (a, b) { return asNumber(a) * asNumber(b); }); },
      transpose: function (args) { var shape = matrixShape(args[0]); var result = []; for (var column = 0; column < shape.columns; column += 1) { result[column] = []; for (var row = 0; row < shape.rows; row += 1) result[column][row] = args[0][row][column]; } return result; },
      reshape: function (args) { var flat = [].concat.apply([], args[0] || []); return matrix.new([args[1], args[2], null]).map(function (row, rowIndex) { return row.map(function (value, columnIndex) { return flat[rowIndex * Number(args[2]) + columnIndex]; }); }); },
      fill: function (args) { var shape = matrixShape(args[0]); for (var row = 0; row < shape.rows; row += 1) for (var column = 0; column < shape.columns; column += 1) args[0][row][column] = args[1]; return args[0]; },
      copy: function (args) { return (args[0] || []).map(function (row) { return row.slice(); }); },
      sort: function (args) { return (args[0] || []).slice().sort(function (a, b) { return array.sum([a]) - array.sum([b]); }); },
      reverse: function (args) { return (args[0] || []).slice().reverse(); },
      sum: function (args) { return [].concat.apply([], args[0] || []).reduce(function (a, b) { return a + asNumber(b); }, 0); },
      avg: function (args) { var values = [].concat.apply([], args[0] || []).map(asNumber).filter(Number.isFinite); return values.length ? values.reduce(function (a, b) { return a + b; }, 0) / values.length : NaN; },
      min: function (args) { return Math.min.apply(Math, [].concat.apply([], args[0] || []).map(asNumber)); },
      max: function (args) { return Math.max.apply(Math, [].concat.apply([], args[0] || []).map(asNumber)); },
      median: function (args) { return array.median([].concat.apply([], args[0] || [])); },
      mode: function (args) { return array.mode([].concat.apply([], args[0] || [])); },
      pow: function (args) { return (args[0] || []).map(function (row) { return row.map(function (value) { return Math.pow(asNumber(value), asNumber(args[1])); }); }); },
      det: function () { return NaN; }, inv: function () { return NaN; }, pinv: function () { return NaN; }, rank: function () { return NaN; }, trace: function () { return NaN; }
    };
    function matrixBinary(left, right, mapper) { var rows = Math.min((left || []).length, (right || []).length); var result = []; for (var row = 0; row < rows; row += 1) { var columns = Math.min((left[row] || []).length, (right[row] || []).length); result[row] = []; for (var column = 0; column < columns; column += 1) result[row][column] = mapper(left[row][column], right[row][column]); } return result; }
    ['float', 'int', 'bool', 'string'].forEach(function (type) { matrix['new_' + type] = matrix.new; });
    function createMap() { return { __pineMap: true, data: Object.create(null) }; }
    var map = {
      new: function () { return createMap(); },
      size: function (args) { return args[0] && args[0].__pineMap ? Object.keys(args[0].data).length : 0; },
      put: function (args) { if (args[0] && args[0].__pineMap) args[0].data[String(args[1])] = args[2]; return args[0]; },
      get: function (args) { return args[0] && args[0].__pineMap ? args[0].data[String(args[1])] : NaN; },
      remove: function (args) { if (args[0] && args[0].__pineMap) { var key = String(args[1]); var value = args[0].data[key]; delete args[0].data[key]; return value; } return NaN; },
      clear: function (args) { if (args[0] && args[0].__pineMap) args[0].data = Object.create(null); return args[0]; },
      contains: function (args) { return !!(args[0] && args[0].__pineMap && Object.prototype.hasOwnProperty.call(args[0].data, String(args[1]))); },
      keys: function (args) { return args[0] && args[0].__pineMap ? Object.keys(args[0].data) : []; },
      values: function (args) { return args[0] && args[0].__pineMap ? Object.keys(args[0].data).map(function (key) { return args[0].data[key]; }) : []; },
      copy: function (args) { var copy = createMap(); if (args[0] && args[0].__pineMap) Object.keys(args[0].data).forEach(function (key) { copy.data[key] = args[0].data[key]; }); return copy; }
    };
    var plotStyles = {
      style_line: 'line', style_histogram: 'histogram', style_columns: 'histogram', style_area: 'area',
      style_circles: 'line', style_cross: 'line'
    };
    var plotNamespace = { style_line: 'line', style_histogram: 'histogram', style_columns: 'histogram', style_area: 'area', style_circles: 'line', style_cross: 'line' };
    math.acos = function (args) { return mapSeries(args[0], Math.acos); };
    math.asin = function (args) { return mapSeries(args[0], Math.asin); };
    math.atan = function (args) { return mapSeries(args[0], Math.atan); };
    math.avg = function (args) { return binarySeries(args[0], args[1], function (a, b) { return (asNumber(a) + asNumber(b)) / 2; }); };
    math.cos = function (args) { return mapSeries(args[0], Math.cos); };
    math.exp = function (args) { return mapSeries(args[0], Math.exp); };
    math.random = function (args) { return args.length ? Math.abs(Math.sin(asNumber(args[0]) || 1)) : 0.5; };
    math.round_to_mintick = function (args) { var value = asNumber(args[0]); var tick = asNumber(args[1]) || 0.01; return Number.isFinite(value) ? Math.round(value / tick) * tick : NaN; };
    math.sign = function (args) { return mapSeries(args[0], function (value) { return value > 0 ? 1 : value < 0 ? -1 : 0; }); };
    math.sin = function (args) { return mapSeries(args[0], Math.sin); };
    math.tan = function (args) { return mapSeries(args[0], Math.tan); };
    math.todegrees = function (args) { return mapSeries(args[0], function (value) { return value * 180 / Math.PI; }); };
    math.toradians = function (args) { return mapSeries(args[0], function (value) { return value * Math.PI / 180; }); };
    math.sum = function (args) { return rollingSum(args[0], Math.max(1, Math.floor(asNumber(args[1])) || 1)); };
    math.e = Math.E; math.pi = Math.PI; math.phi = (1 + Math.sqrt(5)) / 2;
    return { ta: ta, math: math, str: str, array: array, matrix: matrix, map: map, color: color, plot: plotNamespace, plotStyles: plotStyles };
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
      if (type === 'time') value = Math.floor(asNumber(value));
      if (type === 'int' || type === 'float' || type === 'time') {
        if (args.named.minval != null && Number.isFinite(Number(args.named.minval))) value = Math.max(Number(args.named.minval), value);
        if (args.named.maxval != null && Number.isFinite(Number(args.named.maxval))) value = Math.min(Number(args.named.maxval), value);
      }
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
      color: function (args) { return registerInput('color', args, '#2563eb'); },
      time: function (args) { return registerInput('time', args, Date.now()); },
      symbol: function (args) { return registerInput('string', args, ''); },
      session: function (args) { return registerInput('string', args, 'regular'); },
      enum: function (args) { return registerInput('string', args, ''); },
      text_area: function (args) { return registerInput('string', args, ''); }
    };
    var env = {
      open: baseSeries(bars, 'open'), high: baseSeries(bars, 'high'), low: baseSeries(bars, 'low'),
      close: baseSeries(bars, 'close'), volume: baseSeries(bars, 'volume'), bar_index: null, time: null, hl2: null, hlc3: null, ohlc4: null,
      na: NaN, true: true, false: false, ta: namespaces.ta, math: namespaces.math, str: namespaces.str,
      array: namespaces.array, matrix: namespaces.matrix, map: namespaces.map, color: namespaces.color,
      plot: namespaces.plot, input: namespaces.input, display: { all: 'all', none: 'none', data_window: 'data_window', pane: 'pane' }
    };
    env.hl2 = binarySeries(env.high, env.low, function (high, low) { return (high + low) / 2; });
    env.bar_index = new PineSeries(function (index) { return index; }, 'bar_index');
    env.time = new PineSeries(function (index) { return securityTime(bars[index], index); }, 'time');
    env.hlc3 = new PineSeries(function (index) { return (env.high.get(index) + env.low.get(index) + env.close.get(index)) / 3; });
    env.ohlc4 = new PineSeries(function (index) { return (env.open.get(index) + env.high.get(index) + env.low.get(index) + env.close.get(index)) / 4; });
    var inputNamespace = namespaces.input;
    var genericInput = function (args) { return registerInput('string', args, ''); };
    Object.keys(inputNamespace).forEach(function (key) { genericInput[key] = inputNamespace[key]; });
    env.input = genericInput;
    function timeSeriesMapper(mapper, source) { return mapSeries(source || env.time, function (value) { var date = new Date(Number(value)); return Number.isFinite(date.getTime()) ? mapper(date) : NaN; }); }
    env.timestamp = function (args) {
      if (args.length >= 3) return Date.UTC(Number(args[0]), Number(args[1]) - 1, Number(args[2]), Number(args[3] || 0), Number(args[4] || 0), Number(args[5] || 0));
      var parsed = Date.parse(String(args[0] || ''));
      return Number.isFinite(parsed) ? parsed : NaN;
    };
    env.dayofmonth = function (args) { return timeSeriesMapper(function (date) { return date.getUTCDate(); }, args[0]); };
    env.dayofweek = function (args) { return timeSeriesMapper(function (date) { return date.getUTCDay() || 7; }, args[0]); };
    env.hour = function (args) { return timeSeriesMapper(function (date) { return date.getUTCHours(); }, args[0]); };
    env.minute = function (args) { return timeSeriesMapper(function (date) { return date.getUTCMinutes(); }, args[0]); };
    env.month = function (args) { return timeSeriesMapper(function (date) { return date.getUTCMonth() + 1; }, args[0]); };
    env.second = function (args) { return timeSeriesMapper(function (date) { return date.getUTCSeconds(); }, args[0]); };
    env.year = function (args) { return timeSeriesMapper(function (date) { return date.getUTCFullYear(); }, args[0]); };
    env.weekofyear = function (args) { return timeSeriesMapper(function (date) { var start = new Date(Date.UTC(date.getUTCFullYear(), 0, 1)); return Math.ceil((((date - start) / 86400000) + start.getUTCDay() + 1) / 7); }, args[0]); };
    env.time_close = env.time;
    env.time_tradingday = env.time;
    env.last_bar_index = Math.max(0, bars.length - 1);
    env.last_bar_time = bars.length ? securityTime(bars[bars.length - 1], bars.length - 1) : NaN;
    env.timenow = Date.now();
    env.syminfo = {
      basecurrency: String(options.basecurrency || ''), currency: String(options.currency || ''), description: String(options.description || options.symbol || ''),
      main_tickerid: String(options.symbol || ''), mintick: Number(options.mintick) || 0.01, pointvalue: Number(options.pointvalue) || 1,
      prefix: String(options.symbol || '').split(':')[0] || '', root: String(options.symbol || '').split(':').pop() || '', session: String(options.session || 'regular'),
      ticker: String(options.symbol || '').split(':').pop() || '', tickerid: String(options.symbol || ''), timezone: String(options.timezone || 'UTC'), type: String(options.symbolType || 'stock')
    };
    var timeframeName = String(options.timeframe || options.period || 'D').toUpperCase();
    env.timeframe = {
      isseconds: /S$/.test(timeframeName), isminutes: /\d*M$/.test(timeframeName), isintraday: /[SMH]/.test(timeframeName),
      isdaily: timeframeName === 'D' || timeframeName === '1D', isweekly: timeframeName === 'W' || timeframeName === '1W', ismonthly: timeframeName === 'M' || timeframeName === '1M',
      isdwm: /^(D|W|M|1D|1W|1M)$/.test(timeframeName), multiplier: parseInt(timeframeName, 10) || 1, main_period: timeframeName, period: timeframeName
    };
    env.barstate = {
      isconfirmed: true, ishistory: true, isrealtime: false, isnew: true,
      isfirst: new PineSeries(function (index) { return index === 0; }),
      islast: new PineSeries(function (index) { return index === bars.length - 1; }),
      islastconfirmedhistory: new PineSeries(function (index) { return index === bars.length - 1; })
    };
    env.ticker = {
      new: function (args) { return [args[0], args[1], args[2]].filter(function (value) { return value != null; }).join(':'); },
      modify: function (args) { return args[0]; }, standard: function (args) { return args[0]; }, heikinashi: function (args) { return String(args[0] || '') + ':HA'; },
      renko: function (args) { return String(args[0] || '') + ':RENKO'; }, kagi: function (args) { return String(args[0] || '') + ':KAGI'; },
      linebreak: function (args) { return String(args[0] || '') + ':LINEBREAK'; }, pointfigure: function (args) { return String(args[0] || '') + ':P&F'; }, inherit: function (args) { return args[0]; }
    };
    function taLength(args, position, fallback) {
      var value = args.named.length != null ? args.named.length : args[position];
      return Math.max(1, Math.floor(asNumber(value)) || fallback);
    }
    function taSource(args, position, fallback) { return args.named.source != null ? args.named.source : (args[position] == null ? fallback : args[position]); }
    function trueRangeForIndex(index) { return trueRangeSeries(env.high, env.low, env.close, true).get(index); }
    namespaces.ta.tr = function (args) { return trueRangeSeries(env.high, env.low, env.close, args.named.handle_na === true); };
    namespaces.ta.atr = function (args) { return rollingRma(namespaces.ta.tr({ named: { handle_na: true } }), taLength(args, 0, 14)); };
    namespaces.ta.stoch = function (args) {
      var source = taSource(args, 0, env.close);
      var high = args[1] || env.high;
      var low = args[2] || env.low;
      return stochasticSeries(source, high, low, taLength(args, 3, 14));
    };
    namespaces.ta.wpr = function (args) { return binarySeries(stochasticSeries(env.close, env.high, env.low, taLength(args, 0, 14)), 100, function (value) { return value - 100; }); };
    namespaces.ta.willr = namespaces.ta.wpr;
    namespaces.ta.mfi = function (args) {
      var length = taLength(args, 0, 14);
      var typical = env.hlc3;
      var positive = new PineSeries(function (index) { return valueAt(typical, index) > valueAt(typical, index - 1) ? asNumber(valueAt(typical, index)) * asNumber(valueAt(env.volume, index)) : 0; });
      var negative = new PineSeries(function (index) { return valueAt(typical, index) < valueAt(typical, index - 1) ? asNumber(valueAt(typical, index)) * asNumber(valueAt(env.volume, index)) : 0; });
      return binarySeries(rollingSum(positive, length), rollingSum(negative, length), function (up, down) { return down === 0 ? 100 : 100 - 100 / (1 + up / down); });
    };
    namespaces.ta.cmo = function (args) {
      var source = taSource(args, 0, env.close);
      var length = taLength(args, 1, 14);
      var change = namespaces.ta.change({ 0: source, 1: 1, named: {} });
      var gains = mapSeries(change, function (value) { return Math.max(0, asNumber(value)); });
      var losses = mapSeries(change, function (value) { return Math.max(0, -asNumber(value)); });
      return binarySeries(rollingSum(gains, length), rollingSum(losses, length), function (up, down) { return up + down === 0 ? 0 : 100 * (up - down) / (up + down); });
    };
    namespaces.ta.ppo = function (args) {
      var source = taSource(args, 0, env.close);
      var fast = taLength(args, 1, 12);
      var slow = Math.max(fast + 1, taLength(args, 2, 26));
      return binarySeries(rollingEma(source, fast), rollingEma(source, slow), function (shortValue, longValue) { return longValue === 0 ? NaN : (shortValue - longValue) / longValue * 100; });
    };
    function bollingerSeries(args) {
      var source = taSource(args, 0, env.close);
      var length = taLength(args, 1, 20);
      var multiplier = asNumber(args.named.mult != null ? args.named.mult : args[2]);
      if (!Number.isFinite(multiplier)) multiplier = 2;
      var basis = rollingSma(source, length);
      var deviation = standardDeviation(source, length);
      return [basis, binarySeries(basis, deviation, function (middle, spread) { return middle + spread * multiplier; }), binarySeries(basis, deviation, function (middle, spread) { return middle - spread * multiplier; })];
    }
    namespaces.ta.bb = bollingerSeries;
    namespaces.ta.bbw = function (args) { var bands = bollingerSeries(args); return binarySeries(binarySeries(bands[1], bands[2], function (upper, lower) { return upper - lower; }), bands[0], function (width, basis) { return basis === 0 ? NaN : width / basis; }); };
    function keltnerSeries(args) {
      var source = taSource(args, 0, env.close);
      var length = taLength(args, 1, 20);
      var multiplier = asNumber(args.named.mult != null ? args.named.mult : args[2]);
      if (!Number.isFinite(multiplier)) multiplier = 1;
      var basis = rollingEma(source, length);
      var spread = rollingEma(namespaces.ta.tr({ named: { handle_na: true } }), length);
      return [basis, binarySeries(basis, spread, function (middle, range) { return middle + range * multiplier; }), binarySeries(basis, spread, function (middle, range) { return middle - range * multiplier; })];
    }
    namespaces.ta.kc = keltnerSeries;
    namespaces.ta.kcw = function (args) { var bands = keltnerSeries(args); return binarySeries(binarySeries(bands[1], bands[2], function (upper, lower) { return upper - lower; }), bands[0], function (width, basis) { return basis === 0 ? NaN : width / basis; }); };
    namespaces.ta.dmi = function (args) {
      var length = taLength(args, 0, 14);
      var smoothing = taLength(args, 1, 14);
      var plus = new PineSeries(function (index) { var up = env.high.get(index) - env.high.get(index - 1); var down = env.low.get(index - 1) - env.low.get(index); return up > down && up > 0 ? up : 0; });
      var minus = new PineSeries(function (index) { var up = env.high.get(index) - env.high.get(index - 1); var down = env.low.get(index - 1) - env.low.get(index); return down > up && down > 0 ? down : 0; });
      var range = namespaces.ta.atr({ 0: length, named: {} });
      var plusDi = binarySeries(rollingRma(plus, length), range, function (value, atr) { return atr === 0 ? 0 : value / atr * 100; });
      var minusDi = binarySeries(rollingRma(minus, length), range, function (value, atr) { return atr === 0 ? 0 : value / atr * 100; });
      var dx = binarySeries(plusDi, minusDi, function (up, down) { return up + down === 0 ? 0 : Math.abs(up - down) / (up + down) * 100; });
      return [plusDi, minusDi, rollingRma(dx, smoothing)];
    };
    namespaces.ta.supertrend = function (args) {
      var factor = asNumber(args.named.factor != null ? args.named.factor : args[0]);
      if (!Number.isFinite(factor)) factor = 3;
      var length = taLength(args, 1, 10);
      var atr = namespaces.ta.atr({ 0: length, named: {} });
      var line = new PineSeries(function (index) { var midpoint = asNumber(env.hl2.get(index)); var range = asNumber(atr.get(index)); return Number.isFinite(midpoint) && Number.isFinite(range) ? midpoint - factor * range : NaN; });
      var direction = new PineSeries(function (index) { return env.close.get(index) >= line.get(index) ? 1 : -1; });
      return [line, direction];
    };
    namespaces.ta.vhf = function (args) {
      var source = taSource(args, 0, env.close);
      var length = taLength(args, 1, 28);
      return new PineSeries(function (index) { var values = rollingValues(source, length, index); if (!values) return NaN; var highest = Math.max.apply(Math, values); var lowest = Math.min.apply(Math, values); var denominator = 0; for (var i = 0; i < length - 1; i += 1) denominator += Math.abs(values[i] - values[i + 1]); return denominator === 0 ? NaN : (highest - lowest) / denominator; });
    };
    namespaces.ta.vi = function (args) { var length = taLength(args, 0, 14); var tr = namespaces.ta.tr({ named: { handle_na: true } }); var vm = new PineSeries(function (index) { return Math.abs(env.high.get(index) - env.low.get(index - 1)) - Math.abs(env.low.get(index) - env.high.get(index - 1)); }); return [binarySeries(rollingSum(vm, length), rollingSum(tr, length), function (a, b) { return b ? a / b : NaN; }), binarySeries(rollingSum(mapSeries(vm, function (value) { return -value; }), length), rollingSum(tr, length), function (a, b) { return b ? a / b : NaN; })]; };
    namespaces.ta.trix = function (args) { var source = taSource(args, 0, env.close); var length = taLength(args, 1, 18); var ema1 = rollingEma(source, length); var ema2 = rollingEma(ema1, length); var ema3 = rollingEma(ema2, length); return namespaces.ta.roc({ 0: ema3, 1: 1, named: {} }); };
    namespaces.ta.aroon = function (args) { var length = taLength(args, 0, 14); var highs = rollingBars(env.high, length, true); var lows = rollingBars(env.low, length, false); return [mapSeries(highs, function (value) { return (length - value) / length * 100; }), mapSeries(lows, function (value) { return (length - value) / length * 100; })]; };
    namespaces.ta.pivothigh = function (args) { var source = taSource(args, 0, env.high); var left = Math.max(1, Math.floor(asNumber(args[1])) || 5); var right = Math.max(1, Math.floor(asNumber(args[2])) || 5); return new PineSeries(function (index) { var pivot = index - right; var value = asNumber(valueAt(source, pivot)); if (!Number.isFinite(value)) return NaN; for (var offset = 1; offset <= left; offset += 1) if (value <= asNumber(valueAt(source, pivot - offset))) return NaN; for (var future = 1; future <= right; future += 1) if (value <= asNumber(valueAt(source, pivot + future))) return NaN; return value; }); };
    namespaces.ta.pivotlow = function (args) { var source = taSource(args, 0, env.low); var left = Math.max(1, Math.floor(asNumber(args[1])) || 5); var right = Math.max(1, Math.floor(asNumber(args[2])) || 5); return new PineSeries(function (index) { var pivot = index - right; var value = asNumber(valueAt(source, pivot)); if (!Number.isFinite(value)) return NaN; for (var offset = 1; offset <= left; offset += 1) if (value >= asNumber(valueAt(source, pivot - offset))) return NaN; for (var future = 1; future <= right; future += 1) if (value >= asNumber(valueAt(source, pivot + future))) return NaN; return value; }); };
    namespaces.ta.ema2 = namespaces.ta.ema;
    namespaces.ta.dema = function (args) { var source = taSource(args, 0, env.close); var length = taLength(args, 1, 14); var first = rollingEma(source, length); return binarySeries(first, rollingEma(first, length), function (a, b) { return 2 * a - b; }); };
    namespaces.ta.tema = function (args) { var source = taSource(args, 0, env.close); var length = taLength(args, 1, 14); var first = rollingEma(source, length); var second = rollingEma(first, length); var third = rollingEma(second, length); return new PineSeries(function (index) { return 3 * first.get(index) - 3 * second.get(index) + third.get(index); }); };
    namespaces.ta.trima = function (args) { var source = taSource(args, 0, env.close); var length = taLength(args, 1, 14); return rollingSma(rollingSma(source, Math.max(1, Math.ceil(length / 2))), Math.max(1, Math.floor(length / 2))); };
    namespaces.ta.frama = namespaces.ta.ema;
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
    var strategyOrders = [];

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

    env.request = {
      security: function () { return NaN; }, security_lower_tf: function () { return []; }, currency_rate: function () { return NaN; }, dividends: function () { return NaN; },
      earnings: function () { return NaN; }, financial: function () { return NaN; }, quandl: function () { return NaN; }, seed: function () { return NaN; }, splits: function () { return NaN; }
    };
    env.strategy = {
      initial_capital: Number(options.initialCapital) || 100000,
      equity: new PineSeries(function () { return Number(options.initialCapital) || 100000; }),
      position_size: new PineSeries(function () { return 0; }), position_avg_price: new PineSeries(function () { return NaN; }),
      wintrades: 0, losstrades: 0,
      entry: function (args) { strategyOrders.push({ type: 'entry', id: String(args[0] || ''), direction: args[1], quantity: args.named.qty != null ? args.named.qty : args.named.quantity, price: args.named.limit || args.named.stop || null }); return true; },
      order: function (args) { strategyOrders.push({ type: 'order', id: String(args[0] || ''), direction: args[1], quantity: args.named.qty != null ? args.named.qty : args.named.quantity }); return true; },
      exit: function (args) { strategyOrders.push({ type: 'exit', id: String(args[0] || ''), from: args.named.from_entry || args[1] || '' }); return true; },
      close: function (args) { strategyOrders.push({ type: 'close', id: String(args[0] || '') }); return true; }, close_all: function () { strategyOrders.push({ type: 'close_all' }); return true; },
      cancel: function (args) { strategyOrders.push({ type: 'cancel', id: String(args[0] || '') }); return true; }, cancel_all: function () { strategyOrders.push({ type: 'cancel_all' }); return true; },
      risk: { allow_entry_in: function () { return true; } }, opentrades: {}, closedtrades: {}, convert_to_account: function (args) { return args[0]; }, convert_to_symbol: function (args) { return args[0]; }, default_entry_qty: function (args) { return 1; }
    };
    env.strategy.opentrades.entry_id = function () { return ''; }; env.strategy.opentrades.entry_price = function () { return NaN; }; env.strategy.opentrades.entry_size = function () { return 0; };
    env.strategy.closedtrades.entry_id = function () { return ''; }; env.strategy.closedtrades.exit_id = function () { return ''; }; env.strategy.closedtrades.profit = function () { return 0; };
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
    var barColors = [];
    var alerts = [];
    function arrowPlotFunction(args) {
      var source = args[0];
      shapePlots.push({ output: 'arrow' + (shapePlots.length + 1), source: source, title: String(named(args, 'title', 'Plot arrow') || 'Plot arrow'), color: named(args, 'colorup', null), textColor: named(args, 'colordown', null), style: 'arrow', location: 'absolute', text: '', character: '', size: 'normal', opacity: 1, characterMode: false });
      return source;
    }
    function candlePlotFunction(args, barMode) {
      if (plots.length >= MAX_PLOTS) throw new PineError('The script exceeded the maximum of ' + MAX_PLOTS + ' plots.');
      var sourceNames = barMode ? ['open', 'high', 'low', 'close'] : ['open', 'high', 'low', 'close'];
      var sources = sourceNames.map(function (name, position) { return args.named[name] != null ? args.named[name] : args[position]; });
      plots.push({ title: String(named(args, 'title', barMode ? 'Plot bar' : 'Plot candle') || (barMode ? 'Plot bar' : 'Plot candle')), sources: sources, color: named(args, 'color', null), wickColor: named(args, 'wickcolor', null), borderColor: named(args, 'bordercolor', null), lineWidth: 1, type: barMode ? 'bar' : 'candlestick', opacity: 1 });
      return sources[3];
    }
    function barColorFunction(args) { barColors.push({ source: args.named.color != null ? args.named.color : args[0], title: String(named(args, 'title', 'Bar color') || 'Bar color') }); return args[0]; }
    function alertFunction(args) { alerts.push({ condition: args[0], message: String(args.named.message != null ? args.named.message : args[1] || '') }); return args[0]; }
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
    env.alert = alertFunction;
    env.plotarrow = arrowPlotFunction;
    env.plotcandle = function (args) { return candlePlotFunction(args, false); };
    env.plotbar = function (args) { return candlePlotFunction(args, true); };
    env.barcolor = barColorFunction;
    function removeDrawing(args) { var drawing = args[0]; var drawingIndex = pineDrawings.indexOf(drawing); if (drawingIndex !== -1) pineDrawings.splice(drawingIndex, 1); return true; }
    function copyDrawing(args) { var original = args[0]; var copy = original && typeof original === 'object' ? mergeObject(original) : original; if (copy && typeof copy === 'object') pineDrawings.push(copy); return copy; }
    function mergeObject(value) { var copy = {}; Object.keys(value || {}).forEach(function (key) { copy[key] = value[key]; }); return copy; }
    function objectGetter(property) { return function (args) { return args[0] && args[0][property]; }; }
    function objectSetter(property) { return function (args) { if (args[0] && typeof args[0] === 'object') args[0][property] = args[1]; return args[0]; }; }
    env.label = { new: labelNewFunction, delete: removeDrawing, copy: copyDrawing, get_x: objectGetter('x'), get_y: objectGetter('y'), get_text: objectGetter('text'), set_x: objectSetter('x'), set_y: objectSetter('y'), set_xy: function (args) { if (args[0]) { args[0].x = args[1]; args[0].y = args[2]; } return args[0]; }, set_text: objectSetter('text'), set_textcolor: objectSetter('textColor'), set_color: objectSetter('color'), set_style: objectSetter('style'), set_size: objectSetter('size'), set_textalign: objectSetter('textAlign'), set_tooltip: objectSetter('tooltip') };
    env.line = { new: lineNewFunction, delete: removeDrawing, copy: copyDrawing, get_x1: objectGetter('x1'), get_x2: objectGetter('x2'), get_y1: objectGetter('y1'), get_y2: objectGetter('y2'), get_price: function (args) { var line = args[0]; var x = asNumber(args[1]); if (!line || line.x1 === line.x2) return line ? line.y1 : NaN; return line.y1 + (line.y2 - line.y1) * (x - line.x1) / (line.x2 - line.x1); }, set_x1: objectSetter('x1'), set_x2: objectSetter('x2'), set_y1: objectSetter('y1'), set_y2: objectSetter('y2'), set_xy1: function (args) { if (args[0]) { args[0].x1 = args[1]; args[0].y1 = args[2]; } return args[0]; }, set_xy2: function (args) { if (args[0]) { args[0].x2 = args[1]; args[0].y2 = args[2]; } return args[0]; }, set_color: objectSetter('color'), set_style: objectSetter('style'), set_width: objectSetter('width'), set_extend: objectSetter('extend'), get_xloc: objectGetter('xloc'), get_extend: objectGetter('extend') };
    env.box = { new: boxNewFunction, delete: removeDrawing, copy: copyDrawing, get_left: objectGetter('left'), get_top: objectGetter('top'), get_right: objectGetter('right'), get_bottom: objectGetter('bottom'), set_left: objectSetter('left'), set_top: objectSetter('top'), set_right: objectSetter('right'), set_bottom: objectSetter('bottom'), set_bgcolor: objectSetter('background'), set_border_color: objectSetter('borderColor'), set_border_style: objectSetter('borderStyle'), set_border_width: objectSetter('borderWidth'), set_text: objectSetter('text'), set_text_color: objectSetter('textColor'), set_text_size: objectSetter('textSize'), set_text_halign: objectSetter('textHalign'), set_text_valign: objectSetter('textValign') };
    env.linefill = { new: function (args) { var fill = { type: 'linefill', line1: args[0], line2: args[1], color: args[2] || '#2563eb' }; pineDrawings.push(fill); return fill; }, delete: removeDrawing, get_line1: objectGetter('line1'), get_line2: objectGetter('line2') };
    env.polyline = { new: function (args) { var polyline = { type: 'polyline', points: args[0] || [], color: args.named && args.named.line_color || args[1] || '#2563eb', width: args.named && args.named.line_width || 2 }; pineDrawings.push(polyline); return polyline; }, delete: removeDrawing, copy: copyDrawing };
    env.table = { new: function (args) { var table = { type: 'table', position: args[0], columns: Math.max(1, Number(args[1]) || 1), rows: Math.max(1, Number(args[2]) || 1), cells: {} }; pineDrawings.push(table); return table; }, cell: function (args) { if (args[0]) args[0].cells[String(args[1]) + ':' + String(args[2])] = { text: args[3], bgcolor: args[4], textColor: args[5] }; return args[0]; }, cell_set_text: function (args) { if (args[0] && args[0].cells[String(args[1]) + ':' + String(args[2])]) args[0].cells[String(args[1]) + ':' + String(args[2])].text = args[3]; return args[0]; }, cell_set_bgcolor: function (args) { return args[0]; }, cell_set_text_color: function (args) { return args[0]; }, cell_set_text_size: function (args) { return args[0]; }, clear: function (args) { if (args[0]) args[0].cells = {}; return args[0]; }, delete: removeDrawing, get_position: objectGetter('position'), set_position: objectSetter('position') };
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
    env.font = { family_default: 'default', family_monospace: 'monospace' };
    env.format = { price: 'price', volume: 'volume', percent: 'percent', inherited: 'inherited' };
    env.scale = { left: 'left', right: 'right', none: 'none' };
    env.text = { align_left: 'left', align_center: 'center', align_right: 'right' };
    env.alert = Object.assign(env.alert, { freq_once_per_bar: 'once_per_bar', freq_once_per_bar_close: 'once_per_bar_close', freq_all: 'all' });
    env.chart = { is_standard: true, is_heikinashi: false, is_renko: false, is_kagi: false, is_linebreak: false, is_pointfigure: false, is_pnf: false };
    env.runtime = { version: VERSION, max_bars_back: bars.length, error_message: '' };
    env.session = { extended: 'extended', regular: 'regular' };
    env.log = { info: function () { return true; }, warning: function () { return true; }, error: function () { return true; } };
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
          node.callee.object.name === 'request' && ['security', 'security_lower_tf'].indexOf(node.callee.property) !== -1) return true;
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
        if (statement.type === 'for-in') return expressionUsesSeries(statement.iterable) || statementsUseSeries(statement.body);
        if (statement.type === 'while') return expressionUsesSeries(statement.test) || statementsUseSeries(statement.body);
        if (statement.type === 'type-declaration' || statement.type === 'enum-declaration' || statement.type === 'noop') return false;
        return false;
      });
    }
    function executeStatements(statements, scope, index) {
      var lastValue = NaN;
      for (var statementIndex = 0; statementIndex < (statements || []).length; statementIndex += 1) {
        var statement = statements[statementIndex];
        if (statement.type === 'noop') continue;
        if (statement.type === 'type-declaration') {
          scope[statement.name] = { __pineType: true, fields: statement.body, new: function (args) { var object = { __pineObjectType: statement.name }; (statement.body || []).forEach(function (field, fieldIndex) { if (field[1]) object[field[1]] = args[fieldIndex]; else if (field[0]) object[field[0]] = args[fieldIndex]; }); return object; } };
          continue;
        }
        if (statement.type === 'enum-declaration') {
          scope[statement.name] = { __pineEnum: true, members: statement.body.map(function (field, memberIndex) { return field[0]; }).reduce(function (output, member, memberIndex) { output[member] = memberIndex; return output; }, {}) };
          continue;
        }
        if (statement.type === 'declaration') {
          var declaration = statement.expression;
          var declarationName = declaration.callee && declaration.callee.name;
          if (declaration.type !== 'call' || ['indicator', 'study', 'strategy', 'library'].indexOf(declarationName) === -1) throw new PineError('Unsupported declaration.', statement.line, 1);
          var declarationArgs = argumentMap(declaration.args, scope, index);
          metadata.title = String(declarationArgs.named.title || declarationArgs[0] || metadata.title);
          metadata.shorttitle = declarationArgs.named.shorttitle || null;
          metadata.overlay = declarationArgs.named.overlay === true;
          metadata.kind = declarationName === 'strategy' ? 'strategy' : declarationName === 'library' ? 'library' : 'indicator';
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
          if (branchResult.type === 'return' || branchResult.type === 'break' || branchResult.type === 'continue') return branchResult;
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
            if (loopResult.type === 'continue') continue;
            lastValue = loopResult.value;
          }
          if (hadLoopVariable) scope[statement.name] = previousLoopValue;
          else delete scope[statement.name];
          continue;
        }
        if (statement.type === 'for-in') {
          var iterable = evaluate(statement.iterable, scope, index);
          var values = Array.isArray(iterable) ? iterable : (iterable && iterable.__pineMap ? Object.keys(iterable.data).map(function (key) { return iterable.data[key]; }) : []);
          if (values.length > MAX_LOOPS) throw new PineError('The loop exceeded the maximum of ' + MAX_LOOPS + ' iterations.', statement.line, 1);
          var hadIterableVariable = Object.prototype.hasOwnProperty.call(scope, statement.name);
          var previousIterableValue = scope[statement.name];
          for (var iterableIndex = 0; iterableIndex < values.length; iterableIndex += 1) {
            scope[statement.name] = values[iterableIndex];
            var iterableResult = executeStatements(statement.body, scope, index);
            if (iterableResult.type === 'return') {
              if (hadIterableVariable) scope[statement.name] = previousIterableValue;
              else delete scope[statement.name];
              return iterableResult;
            }
            if (iterableResult.type === 'break') { lastValue = iterableResult.value; break; }
            if (iterableResult.type === 'continue') continue;
            lastValue = iterableResult.value;
          }
          if (hadIterableVariable) scope[statement.name] = previousIterableValue;
          else delete scope[statement.name];
          continue;
        }
        if (statement.type === 'while') {
          var whileIterations = 0;
          while (valueAt(evaluate(statement.test, scope, index), index)) {
            whileIterations += 1;
            if (whileIterations > MAX_LOOPS) throw new PineError('The loop exceeded the maximum of ' + MAX_LOOPS + ' iterations.', statement.line, 1);
            var whileResult = executeStatements(statement.body, scope, index);
            if (whileResult.type === 'return') return whileResult;
            if (whileResult.type === 'break') { lastValue = whileResult.value; break; }
            if (whileResult.type !== 'continue') lastValue = whileResult.value;
          }
          continue;
        }
        if (statement.type === 'return') return { type: 'return', value: statement.expression ? evaluate(statement.expression, scope, index) : NaN };
        if (statement.type === 'break') return { type: 'break', value: lastValue };
        if (statement.type === 'continue') return { type: 'continue', value: lastValue };
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
      if (plot.sources) {
        outputs[output] = seriesToPoints(plot.sources[3] || plot.sources[0], bars);
        var sourceOutputs = plot.sources.map(function (source, sourceIndex) {
          var sourceOutput = output + '_' + sourceIndex;
          outputs[sourceOutput] = seriesToPoints(source, bars);
          return sourceOutput;
        });
        render.push({ output: output, sourceOutputs: sourceOutputs, type: plot.type, title: plot.title, color: plot.color, wickColor: plot.wickColor, borderColor: plot.borderColor, lineWidth: plot.lineWidth, opacity: plot.opacity });
        return;
      }
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
    barColors.forEach(function (barColor, index) {
      var output = 'barcolor' + (index + 1);
      outputs[output] = colorSeriesToPoints(barColor.source, bars);
      render.push({ output: output, type: 'barcolor', title: barColor.title });
    });
    alerts.forEach(function (alert, index) {
      var output = 'eventAlert' + (index + 1);
      outputs[output] = seriesToPoints(alert.condition, bars);
      alert.output = output;
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
      securityRequests: securityRequests,
      strategyOrders: strategyOrders,
      alerts: alerts.map(function (alert) { return { output: alert.output, message: alert.message }; })
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
