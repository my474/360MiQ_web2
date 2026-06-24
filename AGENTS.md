# Agent Workflow

- After every turn that changes code, stage the relevant files and create a git commit before the final response, unless the user explicitly asks not to commit.
- For every theme-dependent UI or chart change, implement and verify all three states: initial light-mode load, initial dark-mode load, and live light-to-dark-to-light toggling without a page reload. Page-load-only behavior is not complete. Theme-dependent chart styling must be reapplied from the shared runtime after the `themechange` event and any resulting chart redraws.
