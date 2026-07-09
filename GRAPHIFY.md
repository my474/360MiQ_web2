# Graphify Notes

Graphify should exclude the WordPress install under `blog/`.

Reason: `blog/` is a vendored/runtime WordPress tree with plugins, themes, uploads, logs, and generated files. Including it makes the graph much larger and hides the project-owned PHP, JavaScript, CSS, and catalog code that we actually want Graphify to model.

Keep `blog/` in `.codexignore` before rebuilding Graphify. If the WordPress tree needs separate analysis later, run that as a deliberate one-off instead of folding it into the normal project graph.
