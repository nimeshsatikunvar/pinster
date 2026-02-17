# Pinster

Pinterest-style WordPress theme and download manager for free resume templates.

## Contents

- **pinster/** – Classic WordPress theme with hero search, filters, and masonry grid for resume templates.
- **pinster-download-manager/** – Plugin: custom post type, taxonomies, file management, gated download (email), secure storage, subscribers, and statistics.

## Upload-ready structure

This repository is now organized to mirror WordPress upload targets directly:

- `pinster/` → upload as a **theme** ZIP (`pinster.zip`) or copy to `wp-content/themes/pinster`.
- `pinster-download-manager/` → upload as a **plugin** ZIP (`pinster-download-manager.zip`) or copy to `wp-content/plugins/pinster-download-manager`.
- `Pinster/pinster.zip` and `Pinster/pinster-download-manager.zip` are prebuilt archives for manual upload.

### Create fresh ZIP packages

From the repository root:

```bash
zip -r Pinster/pinster.zip pinster
zip -r Pinster/pinster-download-manager.zip pinster-download-manager
```

## Requirements

- WordPress 5.9+
- PHP 7.4+

## Installation

1. Copy the `pinster` folder into `wp-content/themes/`.
2. Copy the `pinster-download-manager` folder into `wp-content/plugins/`.
3. Activate the theme and the Pinster Download Manager plugin.
4. In **Settings → Reading**, set "Your homepage displays" to "A static page" and choose a page as the front page (or use "Your latest posts" so the theme front page is used).

## Plugin features

- **Resume Templates** – Custom post type with thumbnail and file (PDF/DOCX).
- **Categories & Styles** – Taxonomies for filtering on the front end.
- **Direct or gated download** – In plugin Settings, enable "Gated download" to require email and send the file by email.
- **Secure storage** – Option to store files outside the media library (not directly accessible by URL).
- **Subscribers** – List of emails collected via gated downloads.
- **Email template** – Customize subject and body (placeholders: `{site_name}`, `{template_name}`).
- **Statistics** – Download counts per template.

## Theme

- Hero section with search.
- Filters by category and style (chips).
- Masonry-style grid of template cards with download button.
- Single template page with large preview and download.
- Archive for resume templates with same filters and search.

## Engagement and AdSense (time on site)

To keep visitors longer and support ad revenue:

- **Filters** – Work on both the front page and archive; links keep users on the same page with updated results.
- **Gated download** – Download opens the single template page (detailed view) and a modal for email, encouraging one more page view.
- **Related templates** – "You might also like" on each single template to suggest more downloads.
- **Ad placement hooks** – Use these in a child theme or plugin to inject AdSense or other ad code:
  - `pinster_after_hero` – After the hero search section.
  - `pinster_before_template_grid` – Before the main grid.
  - `pinster_single_before_content` – On single template page, before main content.
  - `pinster_before_footer` – Above the footer.
- **CSS class** – `.pinster-ad-slot` is available for ad wrapper styling (min-height, dashed border).

## Coding standards

Run PHP_CodeSniffer with WordPress-Core (and optionally WordPress-Docs) for theme and plugin PHP.
