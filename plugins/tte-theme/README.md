# TTE Frontend

WordPress plugin containing the TTE-specific frontend integration. The site uses
the **USYD Brand** theme directly; this plugin does not provide a WordPress theme,
header, footer, fonts, or global typography.

## Responsibilities

- TTE Omeka archive pages (`translations`, `classics`, `texts`, `persons`,
  `places`, and `stories`)
- Public entity routes such as `/translation/{id}`, `/text/{id}`,
  `/person/{id}`, and `/place/{id}`
- Entity and archive templates
- Featured-record controls
- Page-scoped JavaScript and CSS components

Omeka API classes and mappings remain in `tte-omeka-project`. Gutenberg blocks
remain in `tte-blocks`. Text annotation remains in `TTE-annotation`.

## Installation

1. Place this directory at `wp-content/plugins/tte-theme`.
2. Activate **TTE Frontend**.
3. Activate the **USYD Brand** theme.
4. Visit **Settings > Permalinks** once if the entity routes have not refreshed.

Activation creates a small `tte-record` renderer page used internally by the
public entity rewrite rules. Do not add that page to navigation.

## Styling rule

All plugin CSS must remain below `.tte-project` and target TTE component classes.
Do not add global selectors for `body`, fonts, links, headings, paragraphs, or
generic WordPress blocks; those belong to USYDBRAND.

