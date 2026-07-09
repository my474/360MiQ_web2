# Graphify Notes

Graphify should exclude the WordPress install under `blog/`.

Reason: `blog/` is a vendored/runtime WordPress tree with plugins, themes, uploads, logs, and generated files. Including it makes the graph much larger and hides the project-owned PHP, JavaScript, CSS, and catalog code that we actually want Graphify to model.

Keep `blog/` in `.codexignore` before rebuilding Graphify. If the WordPress tree needs separate analysis later, run that as a deliberate one-off instead of folding it into the normal project graph.

## Update Workflow

When Graphify needs to be updated, read this file first and confirm `.codexignore` still excludes `blog/`. Do not start by scanning the whole project blindly; use this note as the project-specific Graphify guide so vendored WordPress files stay out of the normal graph.

Update Graphify only when project-owned source files or Graphify inputs have changed enough that the cache is stale. After rebuilding, verify that `graphify-out/cache` contains no entries whose `source_file` starts with `blog\` before staging the cache.
