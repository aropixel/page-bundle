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
