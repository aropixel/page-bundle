# CLAUDE.md — PageBundle

> **IMPORTANT — maintenance safeguard**
> This file documents implicit contracts that are not apparent from reading the code.
> **Any change to an invariant listed here must be reflected here immediately.**
> A stale CLAUDE.md is actively misleading — better to delete it than let it lie.

## Documentation

- [Index](doc/index.md)
- [Installation](doc/installation.md)
- [Entities](doc/entities.md)
- [Usage — page types, builder, custom](doc/usage.md)
- [Custom blocks (page builder)](doc/custom-blocks.md)
- [i18n](doc/i18n.md)

---

## Non-obvious invariants

### `Page` (MappedSuperclass)

- `Page` is `#[ORM\MappedSuperclass]` — instantiate directly with `new Page()`.

### Page types

| Constant / value | Content field used | Notes |
|---|---|---|
| `Page::TYPE_DEFAULT` (`'default'`) | `htmlContent` (HTML) | Standard wysiwyg page |
| `Page::TYPE_BUILDER` (`'builder'`) | `jsonContent` (JSON) | Visual drag-and-drop editor |
| Any other string (e.g. `'contact'`) | `jsonContent` (JSON) | Custom page — free JSON format |

### Fixed pages (`staticCode` + `isDeletable`)

System pages are identified by `staticCode` (unique in the database) and marked as non-deletable:

```php
$page->setStaticCode('homepage');
$page->setIsDeletable(false);
```

Do not set `staticCode` on regular pages — the unique constraint forbids duplicates.

### `PageTranslation` — constructor signature

```php
// locale and field are required (unlike PostCategoryTranslation)
new PageTranslation(string $locale, string $field, ?string $value = null)
```

### Translation pattern

Same rule as BlogBundle:

```php
$page->setTitle('English title');   // Gedmo fallback
$page->addTranslation(new PageTranslation('fr', 'title', 'Titre français'));
```

### Custom page types (arbitrary JSON type)

For a custom page type to be editable in the admin:

1. Create a form type extending `AbstractJsonPageType`:
   ```php
   #[AutoconfigureTag('aropixel.page.form_type')]
   class ContactPageType extends AbstractJsonPageType
   {
       public function getType(): string { return 'contact'; }
       protected function buildCustomForm(FormBuilderInterface $builder, array $options): void { ... }
   }
   ```
2. Create the override template at `templates/bundles/AropixelPageBundle/{type}/form.html.twig`.

The value returned by `getType()` must match exactly the `type` set on the `Page` entity.

### Front page URLs are an application concern

The renderers never name a front-office route. A button block or a clickable column carrying
`linkType: 'page'` is resolved through `PageUrlGeneratorInterface`, whose default implementation
(`RoutePageUrlGenerator`) generates the route configured under `page_builder.front_route`
(`name` / `parameter` / `include_parent`, defaulting to `front_page_show` + `fullPath` + hierarchical).

Two invariants:

- **Never hardcode a route name in a renderer.** The bundle has no way to know how a host
  application routes its pages; doing so makes the bundle unusable anywhere the name differs.
- **A URL that cannot be generated returns `null`, never throws.** `RoutePageUrlGenerator` catches
  routing exceptions and logs a warning. Saving a page must not fail because one link is
  misconfigured — callers fall back to the block's raw `url`.

### The bundle ships no style of its own

`title_styles` and `button_colors` are **empty by default**, and each block reads its own list from
`page_builder_config` at inspector render time (`assets/page_builder/style_options.js`). A style is a
CSS class only the host application's stylesheet can define, so a list hardcoded in the bundle would
offer authors options that render as nothing.

Three invariants:

- **Never hardcode a style, colour or class list in a block type.** Read the configured list.
- **An empty list hides its selector**, it does not fall back to defaults.
- **A saved value absent from the configuration is kept and flagged**, never silently replaced —
  rendering the inspector must not mutate a page's content.

### `allowed_blocks` is enforced server-side, not just in the library

Filtering the library (`_library.html.twig`) is presentation. The list is actually opposed in
`BlockPolicy`, called by `SaveAction`, which rejects a payload carrying a forbidden type with a 400.

- **Never treat the library as the guarantee.** A payload arrives as JSON; anything can be in it.
- **`BlockPolicy::findForbidden()` must walk nested rows** (`nested-row` blocks carry their own
  `row`), otherwise one level of nesting bypasses the whitelist.
- The library captures each tab's cards into a variable (`pb_pane_*`) and skips the tab when the
  capture is empty. The capture holds the cards only, **not** the `.row` wrapper — wrapping it would
  make every tab look non-empty.

### The admin loads no third-party asset from a CDN

Quill comes from AdminBundle, in one version: `quill.js` and `quill.snow.css` from its layout,
`quill.bubble.css` added by the builder's own `stylesheets` block. A back-office that depends on a
CDN goes down with it, and a strict CSP is enough to break the editor.

**Known exception, not yet fixed:** `builder/preview.html.twig` still pulls Bootstrap and UIkit from
jsDelivr. Serving them locally is not the whole answer — the preview should render with the host
application's own stylesheet, otherwise it shows a generic framework rather than the site being
edited.

### Page access is the application's call, and denial is a 404

Every page the bundle loads by id goes through `PageAccessCheckerInterface` — `VIEW` for reads,
`EDIT` for writes, `DELETE` for removal. The default implementation grants everything; an
application with pages that do not all belong to the same audience replaces the service.

- **Never load a page by id and act on it without asking the checker.** An id in a request proves
  nothing. This covers `SaveAction`, `BuilderAction`, `PreviewAction`, `Default\EditAction`,
  `StatusAction` and `DeleteAction`.
- **Listings filter, they do not fail.** `ListAction` and `Builder\JsonListAction` drop the entries
  the checker refuses, so a listing never reveals a page its reader may not open.
- **Denial is `NotFoundHttpException`, never `AccessDeniedException`** — a 403 confirms that the page
  exists.
- Page *creation* is out of scope: there is no entity to check yet.

### Renderers read content, not a contract

A payload is content: it may have been written by hand, or saved by an older builder that did not
yet set a key the JS models set today. **Every read of a payload key goes through `??`** (or a prior
`isset`/`empty` guard) in both renderers — a missing key is normal, not exceptional, and rendering
must never depend on which version of the editor wrote the page.

`RendererTolerantPayloadTest` renders the bare minimum through both renderers with an error handler
installed, and fails on any warning.

### A custom block needs a renderer, or it vanishes

`page_builder.custom_blocks` plus its JavaScript make a block editable — library card, preview,
inspector, stored JSON. **None of that renders it.** The renderers hand unknown types to the
autoconfigured `CustomBlockRendererInterface` implementations; with none claiming the type, the
block renders as an empty string and disappears from the page.

- **Never add a custom block without its renderer**, or authors will build something that does not
  show up.
- The first implementation whose `supports()` returns true wins; escaping is the implementation's.

### `title_styles` values are parsed, not opaque

A style value reads `tag-class_class_size`: split **once** on `-` for the HTML tag, then on `_` for
the classes and an optional trailing pixel size. **A class name containing an underscore breaks it** —
`div-footer__text_14` yields the classes `footer`, `` (empty) and `text`.

Both the JS block and the PHP renderers parse it, and they must agree. They did not until 2026-09-11:
the JS split on every hyphen and kept the second piece, so `div-footer-heading_16` applied the class
`footer` in the canvas and `footer-heading` in the page. Any change to the format belongs in both.

### The builder's URLs must survive an admin that carries context

`save_url`, `preview_url` and `json_list_url` override the routes the builder template generates,
each defaulting to the bundle's own. They exist because an application whose admin carries context in
the URL (a tenant id, say) would otherwise post its saves to a context-free URL — and
`PageAccessCheckerInterface` would then judge the page against the wrong context.

**Never hardcode a `path()` for a URL the browser will call back**: an application may need to put
something in it.


### Section colours: text inherits, links need a rule

`section.textColor` is written as `color` on the section and inherited by every block.
`section.linkColor` cannot be — no inline style can target `a` — so it is exposed as
`--pb-link-color` and the host stylesheet must spend one rule on it. The canvas does the same, so
what an author sees is what the page renders.

**The bundle ships no colour presets.** It used to offer six hardcoded swatches from another
project's palette; a colour belongs to the host application, exactly like `title_styles` and
`button_colors`.
