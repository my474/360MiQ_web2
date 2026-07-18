# Graphify Notes

Graphify should exclude the WordPress install under `blog/`.

Reason: `blog/` is a vendored/runtime WordPress tree with plugins, themes, uploads, logs, and generated files. Including it makes the graph much larger and hides the project-owned PHP, JavaScript, CSS, and catalog code that we actually want Graphify to model.

Keep `blog/` in `.codexignore` before rebuilding Graphify. If the WordPress tree needs separate analysis later, run that as a deliberate one-off instead of folding it into the normal project graph.

## Update Workflow

When Graphify needs to be updated, read this file first and confirm `.codexignore` still excludes `blog/`. Do not start by scanning the whole project blindly; use this note as the project-specific Graphify guide so vendored WordPress files stay out of the normal graph.

Update Graphify only when project-owned source files or Graphify inputs have changed enough that the cache is stale. After rebuilding, verify that `graphify-out/cache` contains no entries whose `source_file` starts with `blog\` before staging the cache.

## Current Normal Scope

Normal Graphify updates should cover project-owned code files only:

- `*.php`
- `*.js`
- `*.jsx`
- `*.ts`
- `*.tsx`

Skip these directories during normal freshness checks and rebuilds:

- `.git/`
- `blog/`
- `graphify-out/`

The current generated graph is expected to have no `blog/` source paths. If WordPress needs analysis, create a separate one-off graph for `blog/` instead of mixing it into the normal project graph.

## Fast Freshness Check

Use this check before rebuilding. It compares the current non-WordPress source files with `graphify-out/graph.json`.

```powershell
@'
const fs = require('fs');
const path = require('path');
const root = process.cwd();
const exts = new Set(['.php', '.js', '.jsx', '.ts', '.tsx']);
const skipDirs = new Set(['.git', 'blog', 'graphify-out']);

function walk(dir, out = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (skipDirs.has(entry.name)) continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full, out);
    else if (exts.has(path.extname(entry.name).toLowerCase())) {
      out.push(path.relative(root, full).replaceAll('\\', '/'));
    }
  }
  return out;
}

const current = new Set(walk(root));
const graph = JSON.parse(fs.readFileSync('graphify-out/graph.json', 'utf8'));
const graphFiles = new Set((graph.nodes || [])
  .map((node) => node.source_file)
  .filter(Boolean)
  .map((file) => String(file).replaceAll('\\', '/')));
const missing = [...current].filter((file) => !graphFiles.has(file)).sort();
const extra = [...graphFiles]
  .filter((file) => !current.has(file) && !file.startsWith('blog/'))
  .sort();
const blog = [...graphFiles].filter((file) => file.startsWith('blog/')).sort();

console.log(JSON.stringify({
  currentFiles: current.size,
  graphFiles: graphFiles.size,
  missingCount: missing.length,
  extraCount: extra.length,
  blogCount: blog.length,
  missing,
  extra
}, null, 2));
'@ | node
```

If `missingCount`, `extraCount`, and `blogCount` are all `0`, Graphify is current for the normal project scope.

## Rebuild Options

Preferred rebuild, when the Python `graphify` module is available:

```powershell
python3 -c "from graphify.watch import _rebuild_code; from pathlib import Path; _rebuild_code(Path('.'))"
```

If the `graphify` module is not available in this workspace, rebuild from the clean cache and add cache entries for missing current source files. Preserve the same normal scope above, and do not include WordPress. After any rebuild, rerun the fast freshness check and the WordPress cache check below.

## Required Verification Before Staging

Check the main graph:

```powershell
@'
const fs = require('fs');
const graph = JSON.parse(fs.readFileSync('graphify-out/graph.json', 'utf8'));
const files = [...new Set((graph.nodes || [])
  .map((node) => node.source_file)
  .filter(Boolean)
  .map((file) => String(file).replaceAll('\\', '/')))];

const blogEntries = files.filter((file) => file.startsWith('blog/'));
console.log(JSON.stringify({
  sourceFiles: files.length,
  nodes: (graph.nodes || []).length,
  links: (graph.links || []).length,
  blogEntries: blogEntries.length
}, null, 2));
if (blogEntries.length) process.exit(1);
'@ | node
```

Check the cache:

```powershell
Get-ChildItem -Path graphify-out\cache -Filter *.json | ForEach-Object {
  $json = Get-Content -Raw -Path $_.FullName | ConvertFrom-Json
  $json.nodes | Where-Object {
    ($_.source_file -as [string]) -like 'blog\*' -or
    ($_.source_file -as [string]) -like 'blog/*'
  } | ForEach-Object { $_.source_file }
}
```

The cache check should print nothing.
