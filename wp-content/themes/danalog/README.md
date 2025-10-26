# Danalog WordPress Block Theme

The Danalog theme is a custom WordPress block theme for the Danalog luxury watches and perfume maison. It ships with curated global styles, self-hosted font loading, and starter block templates ready for editorial and commerce content.

## Requirements

- WordPress 6.4 or later
- PHP 8.0 or later
- WooCommerce (optional, but the theme enables support)

## Installation

1. Copy or deploy the theme folder to your WordPress installation:
   ```bash
   wp-content/themes/danalog
   ```
2. Replace the placeholder font files in `assets/fonts/` with the licensed WOFF2 versions supplied by the Danalog brand team. Update `assets/fonts/fonts.css` if filenames differ.
3. In wp-admin, navigate to **Appearance → Themes** and activate **Danalog**.
4. Assign menus to the Primary, Secondary, and Footer menu locations under **Appearance → Menus** or the Site Editor.
5. Set the "Front page" to use the "Front Page" template via **Settings → Reading** if custom homepage routing is required.

## Editing & Customisation

- All color, typography, and spacing tokens are defined in `theme.json` for consistent editing in the Site Editor.
- Base UI utilities (buttons, cards, forms, links) live in `assets/css/base.css`.
- Template parts can be updated in the Site Editor or edited directly under `parts/`.
- Templates for the front page, single pages, and the index/blog archive are included under `templates/`.

## Fonts

The theme expects two self-hosted font files:

- `assets/fonts/Manrope-Variable.woff2`
- `assets/fonts/SeigneurSerifDisplay-Regular.woff2`

Replace the provided placeholders with production-ready WOFF2 files, then clear caching layers to ensure visitors receive the latest assets.
