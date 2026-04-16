<p align="center">
  <a href="http://www.aropixel.com/">
    <img src="https://avatars1.githubusercontent.com/u/14820816?s=200&v=4" alt="Aropixel logo" width="75" height="75" style="border-radius:100px">
  </a>
</p>

<h1 align="center">Aropixel Page Bundle</h1>

<p align="center">
  A page management module for Symfony, built as a companion to <a href="https://github.com/aropixel/admin-bundle">Aropixel Admin Bundle</a>.
</p>

<p align="center">
  <img src="https://img.shields.io/github/last-commit/aropixel/page-bundle.svg" alt="Last commit">
  <a href="https://github.com/aropixel/page-bundle/issues"><img src="https://img.shields.io/github/issues/aropixel/page-bundle.svg" alt="Issues"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/aropixel/page-bundle.svg" alt="License"></a>
</p>

---

## Features

- **Three page types**: standard HTML (CKEditor), visual page builder (JSON → pre-rendered HTML), and structured JSON forms
- **Visual page builder** — block-based drag-and-drop editor with sections, rows, columns, text, images, buttons, titles, and more; HTML is pre-rendered at save time so front-end display is zero-cost
- **Custom block types** — extend the page builder with your own blocks via a simple JS + YAML registration
- **Fixed / protected pages** — declare non-deletable system pages (homepage, contact…) with a static code for reliable lookups
- **Multilingual** — full i18n support via Gedmo Translatable; per-locale slugs, content, and pre-rendered HTML
- **SEO fields** — meta title, meta description, and slug per locale
- **Publication scheduling** — online/offline status with optional date range
- **`PageSavedEvent`** — dispatched after every page builder save; use it to invalidate Varnish, CDN, Redis, or any cache layer

---

## Requirements

- PHP 8.2+
- Symfony 6.4 or 7.x
- `aropixel/admin-bundle` installed and configured

---

## Quick start

```bash
composer require aropixel/page-bundle
```

Import the routes in `config/routes.yaml`:

```yaml
aropixel_page:
    resource: "@AropixelPageBundle/src/Resources/config/routes.yaml"
    prefix: /admin/page
```

Run migrations:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

See the [full installation guide](doc/installation.md) for entity extension, Doctrine mapping, and bundle configuration.

---

## Page types

| Type | Storage | Use case |
|---|---|---|
| `TYPE_DEFAULT` | `htmlContent` | Simple pages edited via CKEditor |
| `TYPE_CUSTOM` | `jsonContent` + `htmlContent` (pre-rendered) | Visual page builder |
| Custom JSON | `jsonContent` | Structured forms with named fields (e.g. contact page with phone/address) |

For `TYPE_CUSTOM`, the page builder JSON payload is rendered to HTML at **save time** and stored in `htmlContent`. Front-end display is a simple `{{ page.htmlContent|raw }}` — no rendering overhead per request.

---

## Documentation

- [Installation](doc/installation.md)
- [Usage and page types](doc/usage.md) — page builder config, fixed pages, front-end rendering, events
- [Custom block types](doc/custom-blocks.md)
- [Entity customization](doc/entities.md)

---

## License

Aropixel Page Bundle is released under the [MIT License](LICENSE).
