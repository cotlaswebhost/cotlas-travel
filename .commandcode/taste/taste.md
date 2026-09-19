# Taste

## Coding style

- Prefers plugin-specific, namespaced CSS classes (e.g. `ctd-*`) over auto-generated block/theme classes (like GenerateBlocks' `gb-text-34efae04`); output should use the plugin's own classes so they can be overridden later. Confidence: 0.85
- Prefers extending existing shortcodes/dynamic tags with new opt-in parameters rather than changing their existing default output, keeping current behavior intact. Confidence: 0.6

## Workflow

- When shipping a plugin update, wants the git push + GitHub release steps spelled out as runnable commands. Confidence: 0.55
