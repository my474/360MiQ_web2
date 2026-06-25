# Agent Workflow

- After every turn that changes code, stage the relevant files and create a git commit before the final response, unless the user explicitly asks not to commit.
- For every theme-dependent UI or chart change, implement and verify all three states: initial light-mode load, initial dark-mode load, and live light-to-dark-to-light toggling without a page reload. Page-load-only behavior is not complete. Theme-dependent chart styling must be reapplied from the shared runtime after the `themechange` event and any resulting chart redraws.
- The in-app browser is known to fail in this workspace because Windows denies access while resolving its runtime under `C:\Users\mchan\AppData`. After this specific failure has occurred, do not retry the in-app browser in later turns. Use direct configuration/runtime tests or another already-authorized fallback, and mention the limitation at most once when it materially affects verification.
