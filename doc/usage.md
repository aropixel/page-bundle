# Usage and Page Types

## Page Types

The `AropixelPageBundle` supports two main page types:

### Default Page (`TYPE_DEFAULT`)
This is the standard page where content is stored as HTML. It's best suited for simple pages with a main text area using a WYSIWYG editor like CKEditor.
The property used for this type is `htmlContent`.

### Builder Page (`TYPE_BUILDER`)
This type is designed for complex layouts where content is stored as JSON and edited through the visual drag-and-drop page builder.
The properties used for this type are `jsonContent` (source) and `htmlContent` (pre-rendered at save time).

### Custom JSON Page Type
This type allows you to create structured forms whose data is stored as a JSON object in the `jsonContent` field. Ideal for pages with specific fields (e.g., a "Contact" page with address and phone fields) without needing a full page builder.

**No YAML configuration required.** Any class that extends `AbstractJsonPageType` is automatically discovered by the bundle via Symfony's service autoconfiguration.

To create a custom JSON page type:

1. **Extend `AbstractJsonPageType`**:
   ```php
   namespace App\Form\Type;

   use Aropixel\PageBundle\Form\Type\AbstractJsonPageType;
   use Symfony\Component\Form\Extension\Core\Type\TextType;
   use Symfony\Component\Form\FormBuilderInterface;

   class ContactPageType extends AbstractJsonPageType
   {
       protected function buildCustomForm(FormBuilderInterface $builder, array $options): void
       {
           $builder
               ->add('phone', TextType::class, ['label' => 'Phone Number'])
               ->add('address', TextType::class, ['label' => 'Address'])
           ;
       }

       public function getType(): string
       {
           return 'contact';
       }
   }
   ```
   That's it — the class is automatically registered as the `contact` page type.

2. **Create the form template**:
   The bundle resolves the template via `@AropixelPage/{type}/form.html.twig`.
   Override it for your app by creating `templates/bundles/AropixelPageBundle/contact/form.html.twig`:

   ```twig
   {# templates/bundles/AropixelPageBundle/contact/form.html.twig #}
   {% extends '@AropixelPage/base.html.twig' %}

   {% block tabbable %}
       <li class="nav-item"><a href="#panel-tab1" data-bs-toggle="pill" class="nav-link active"><span>Contact</span></a></li>
       <li class="nav-item"><a href="#panel-tab2" data-bs-toggle="pill" class="nav-link"><span>{% trans %}page.form.seo{% endtrans %}</span></a></li>
   {% endblock %}

   {% block mainPanel %}
       <div class="tab-pane active" id="panel-tab1">
           <div class="card card-centered-large">
               <div class="card-body">
                   {{ form_row(form.title) }}
                   {{ form_row(form.phone) }}
                   {{ form_row(form.address) }}
               </div>
           </div>
       </div>
       <div class="tab-pane" id="panel-tab2">
           <div class="card card-centered-large">
               <div class="card-body">
                   {{ form_row(form.metaTitle) }}
                   {{ form_row(form.metaDescription) }}
                   {{ form_row(form.metaKeywords) }}
               </div>
           </div>
       </div>
   {% endblock %}
   ```

   You can also override `getTemplate()` in your form class to point to any Twig path:
   ```php
   public function getTemplate(): string
   {
       return '@App/admin/page/contact_form.html.twig';
   }
   ```

## Fixed and Protected Pages

You can define "system" pages that should always be present and cannot be accidentally deleted by users.

### 1. Declare a fixed page with `#[AsFixedPage]`

Create one class per fixed page, annotated with the `#[AsFixedPage]` attribute:

```php
// src/FixedPage/HomepageFixedPage.php
namespace App\FixedPage;

use Aropixel\PageBundle\Attribute\AsFixedPage;

#[AsFixedPage(code: 'homepage', title: 'Home')]
class HomepageFixedPage {}
```

```php
// src/FixedPage/ContactFixedPage.php
namespace App\FixedPage;

use Aropixel\PageBundle\Attribute\AsFixedPage;

#[AsFixedPage(code: 'contact', title: 'Contact', type: 'contact')]
class ContactFixedPage {}
```

**Attribute parameters:**

| Parameter | Type | Default | Description |
|---|---|---|---|
| `code` | `string` | required | Unique identifier stored as `staticCode` in the database |
| `title` | `string` | required | Initial page title created on first sync |
| `type` | `string` | `'default'` | Page type (must match a registered form type) |
| `deletable` | `bool` | `false` | Whether users can delete this page from the admin |

As long as the class lives in a directory covered by Symfony's service autodiscovery (typically `src/`), no additional configuration is needed — the bundle detects the attribute automatically via `autoconfigure: true`.

### 2. Synchronize with the database

Run the following command to create or update the fixed pages:

```bash
php bin/console aropixel:page:sync-fixed
```

The `staticCode` is set to the `code` value, allowing you to safely fetch the page in your code:

```php
$homepage = $pageRepository->findOneBy(['staticCode' => 'homepage']);
```

## Custom Block Types

The page builder can be extended with custom blocks defined by the application. See the dedicated guide:

- [Adding Custom Block Types](custom-blocks.md)

---

## Page Builder Configuration

When using the **Custom JSON Page** type with the built-in page builder, you can configure the available style options for certain blocks directly in your Symfony configuration.

### Title block styles

The title block can offer a dropdown of predefined CSS styles. Each style maps a `value` to a
human-readable `label` displayed in the admin interface.

The `value` is written to the block's `size` and drives the rendered markup: it reads as
`tag-class_class_size`, where the leading segment is the HTML tag and a trailing number is a pixel
font size. So `h2-highlight_32` renders `<h2 class="highlight" style="font-size:32px">`, and a bare
`h2` renders `<h2>` with no class.

> **Class names cannot contain an underscore.** The value is split on `_` to separate classes from
> the font size, so a BEM name like `footer__text` yields three classes, one of them empty. Hyphens
> are fine: `footer-text` works, in both the canvas and the rendered page.

### Button block colors

Similarly, the button block can offer a list of predefined color options. Each entry maps a `value` (a CSS class) to a `label`.

### Configuration

In `config/packages/aropixel_page.yaml`:

```yaml
aropixel_page:
    page_builder:
        title_styles:
            - { value: 'h1', label: 'Heading 1' }
            - { value: 'h2', label: 'Heading 2' }
            - { value: 'h2-highlight_32', label: 'Highlighted title' }
        button_colors:
            - { value: 'btn-primary', label: 'Primary' }
            - { value: 'btn-secondary', label: 'Secondary' }
            - { value: 'btn-outline-primary', label: 'Outline' }
```

Both lists are **empty by default**, and an empty list hides its selector entirely — the bundle
defines no CSS of its own, so it ships no style: offering authors a choice that renders as nothing
would be worse than offering none. Declare only what your stylesheet actually provides.

If a page was saved with a style you later removed from the configuration, the value is **kept and
flagged** in the dropdown rather than silently replaced: opening the inspector never rewrites
existing content.

### Restricting access to pages

The bundle loads pages by id, straight from the URL or the save payload. In a single-tenant
application that is fine: reaching the admin is the authorisation. As soon as pages belong to
something narrower — one tenant, one brand, one site of a multi-site install — an id in a request is
not proof that the current user may touch that page.

Implement `PageAccessCheckerInterface` and replace the service:

```php
namespace App\Page;

use Aropixel\PageBundle\Component\Security\PageAccessCheckerInterface;
use Aropixel\PageBundle\Entity\PageInterface;

class TenantPageAccessChecker implements PageAccessCheckerInterface
{
    public function isGranted(string $attribute, PageInterface $page): bool
    {
        return $page->getTenant() === $this->tenantContext->current();
    }
}
```

```yaml
# config/services.yaml
services:
    Aropixel\PageBundle\Component\Security\PageAccessCheckerInterface:
        alias: App\Page\TenantPageAccessChecker
```

Three attributes are checked, and every page the bundle touches goes through one of them:

| Attribute | Where |
|---|---|
| `VIEW` | builder preview, page listings (entries you may not see are filtered out) |
| `EDIT` | builder canvas, builder save, page edit form, status change |
| `DELETE` | page deletion |

A refusal is reported as **404, not 403**: whether a page exists is itself information. The default
implementation grants everything, so an application that does not replace the service behaves exactly
as before.

> **Not covered:** creating a page. There is no entity to check yet, so a project that must restrict
> creation does it in its own controller.

### Assets

The page builder loads no third-party asset from a CDN. Quill comes from AdminBundle — `quill.js`
and `quill.snow.css` through the admin layout, `quill.bubble.css` (the canvas's inline editor)
through the builder's own stylesheets block — all in the same version.

The one remaining exception is the preview window (`builder/preview.html.twig`), which still pulls
Bootstrap and UIkit from a CDN. It is on the list to fix, together with letting a project declare the
stylesheets its preview should use.

### Section colours

A section carries a background — colour, image or CSS class — and, since the background is free, the
colours of what sits on it. A dark background with the site's default ink is unreadable, so the two
are set in the same place.

**Which colours a section offers is yours to decide.** The default is text, titles and links, which
is what most pages need:

```yaml
aropixel_page:
    page_builder:
        section_colors:
            - { key: 'text',  label: 'page.builder.inspector.text_color',  variable: '--pb-text-color', inherited: true }
            - { key: 'title', label: 'page.builder.inspector.title_color', variable: '--pb-title-color' }
            - { key: 'link',  label: 'page.builder.inspector.link_color',  variable: '--pb-link-color' }
```

Each entry adds a picker to the section inspector and writes one custom property on the section.
`label` is a translation key; `key` is how the value is stored in the payload, as `<key>Color`.

`inherited: true` also writes the colour as `color` on the section, so every block picks it up
without a rule — that is the text colour, and a second one would simply overwrite the first. The
others cannot be reached that way, since an inline style cannot target a descendant, so **each needs
one rule from your stylesheet**, once:

```css
.my-page :is(h1, h2, h3, h4, h5, h6) { color: var(--pb-title-color, inherit); }
.my-page a { color: var(--pb-link-color, inherit); }
```

The `inherit` fallback is what makes titles and links follow the text colour until someone gives them
one of their own. Leave a colour empty and nothing is written at all: the site's own styles apply.

> Your rule decides what counts as a title. A theme whose headings are `<div class="heading">` — the
> block's named styles allow it — names that class here instead of, or alongside, `h1`–`h6`.
>
> Adding a fourth colour — buttons, icons, borders — is a line of configuration and a line of CSS;
> the bundle needs no change.

### Restricting the block library

By default authors can use every block the bundle ships. `allowed_blocks` narrows that to a list you
choose:

```yaml
aropixel_page:
    page_builder:
        allowed_blocks: ['text', 'image', 'divider', 'spacer', 'button']
```

An empty list — the default — allows everything. Otherwise:

- the library only renders the allowed cards, and **a tab left with no card is not displayed at
  all**; the first tab still holding a card becomes the active one;
- a save carrying a forbidden block is **rejected with a 400** listing the offending types. The
  library is presentation, and a payload reaches the server as JSON: `BlockPolicy` is where the list
  is actually enforced, nested rows included.

Custom blocks are subject to the same list: declaring one in `custom_blocks` does not exempt it, so
a non-empty `allowed_blocks` must name it too.

> **Scope:** the list is global to the application, not per page type. A project using the builder
> for two purposes — a footer and editorial pages, say — cannot yet allow different blocks for each.

### Requiring a block

Some blocks are not a matter of taste. `required_blocks` names the types a page must carry to be
saved at all:

```yaml
aropixel_page:
    page_builder:
        required_blocks: ['legal-links']
```

A save whose payload does not carry one is **rejected with a 400**, naming the block by its library
label rather than its type — the author who has just deleted it without thinking needs to recognise
it. The block counts wherever it sits, nested rows included.

An empty list — the default — requires nothing. The same scope note as `allowed_blocks` applies.

### Linking to another page

A button block, or a clickable column, can target another page of the site rather than a raw URL. The
builder stores the target's slug (`pagePath`, plus `parentSlug` when the target has a parent) and the
renderer turns it into a URL at save time — which means the bundle needs to know **how your
application routes its pages**.

By default it generates the route `front_page_show` with a `fullPath` parameter, the parent slug
prefixing the page slug. Point it at your own route:

```yaml
aropixel_page:
    page_builder:
        front_route:
            name: 'app_page_show'   # your route name
            parameter: 'slug'       # the parameter receiving the page path
            include_parent: false   # flat URLs (/page/{slug}); true for hierarchical ones (/{fullPath})
```

`include_parent` is the difference between the two usual conventions:

| Convention | Route | `include_parent` | Generated |
|---|---|---|---|
| Hierarchical | `/{fullPath}` | `true` (default) | `about/team` |
| Flat | `/page/{slug}` | `false` | `/page/team` |

A route that cannot be generated — wrong name, missing parameter — produces **no URL and a warning
in the logs**, never an exception: a misconfigured link must not stop an author from saving a page.
The block then falls back to whatever raw `url` it carries.

If neither convention fits — per-host URLs in a multi-tenant application, a locale in the path,
anything that needs more than a route name — implement `PageUrlGeneratorInterface` and replace the
service:

```php
namespace App\Page;

use Aropixel\PageBundle\Component\Builder\PageUrlGeneratorInterface;

class TenantPageUrlGenerator implements PageUrlGeneratorInterface
{
    public function generate(array $data): ?string
    {
        // $data['pagePath'], $data['parentSlug'] - return null when you cannot build a URL
    }
}
```

```yaml
# config/services.yaml
services:
    Aropixel\PageBundle\Component\Builder\PageUrlGeneratorInterface:
        alias: App\Page\TenantPageUrlGenerator
```

### Multilingual support

The page builder locale switcher is driven by the `aropixel_admin.translations.locales` setting in `AdminBundle` — there is no separate locale config in `PageBundle`. See the [AdminBundle i18n documentation](../../admin-bundle/doc/i18n.md) for details.

When two or more locales are configured, the page builder displays a locale switcher and a **"Synchronise other languages with [primary locale]"** checkbox. Structural changes (sections, rows, blocks) are automatically propagated to all secondary locales when sync is enabled; textual content must be translated manually.

> **Note:** When `title_styles` or `button_colors` is empty (the default), the corresponding selector is not shown in the page builder inspector. This means a fresh installation of the bundle ships with no project-specific styles — you define only what your project needs.

## Administrative Interface

Once installed and configured, you'll have access to the page management in the Aropixel Admin interface.

### Creating a Page
1. Navigate to the "Pages" section in the admin panel.
2. Click on "Add Page".
3. Fill in the title, slug, and content.
4. Set the publication status (Online/Offline) and optional scheduling.

### Managing Translations
If your application is configured to be multi-language:
- You'll see a tab for each configured locale in the page edit form.
- Each field can be translated independently.
- The `slug` can also be translated to provide localized URLs.
- `htmlContent` is rendered and stored per locale each time the page builder is saved for that locale.

## Front-end Rendering

In your front-end controller, you can retrieve the page by its slug or ID:

```php
// src/Controller/PageController.php
namespace App\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/page/{slug}', name: 'page_show')]
    public function show(string $slug, EntityManagerInterface $em): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['slug' => $slug]);

        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }
}
```

In your Twig template:

```twig
{# templates/page/show.html.twig #}
<h1>{{ page.title }}</h1>
<div>
    {% if page.type == 'default' %}
        {# HTML stored via CKEditor #}
        {{ page.htmlContent|raw }}
    {% elseif page.type == 'builder' %}
        {# HTML pre-rendered by the page builder at save time #}
        {{ page.htmlContent|raw }}
    {% else %}
        {# Render structured JSON content (e.g., for type 'contact') #}
        {% set data = page.jsonContent|json_decode %}
        <p>Phone: {{ data.phone }}</p>
        <p>Address: {{ data.address }}</p>
    {% endif %}
</div>
```

For **custom pages**, `htmlContent` is automatically populated when the page is saved via the page builder admin. The JSON payload is rendered to HTML at save time by the configured `PageBuilderRendererInterface` implementation — so front-end display requires no rendering work at all.

If you prefer on-the-fly rendering (e.g. when a full-page HTTP cache like Varnish is in front), you can inject `PageBuilderRendererInterface` directly into your controller and call `$renderer->render($page->getJsonContent())` instead.

---

## Events

### `PageSavedEvent` (`aropixel.page.saved`)

Dispatched by `SaveAction` after every successful page builder save. Carries:

| Method | Type | Description |
|---|---|---|
| `getPage()` | `Page` | The saved page entity |
| `getLocale()` | `string` | The locale that was saved |
| `getRenderedHtml()` | `string` | The HTML rendered from the JSON payload |

The bundle dispatches the event but provides **no built-in listener** — this is intentional. You decide how to react to a page save.

#### Example: invalidate a Varnish cache

```php
// src/EventListener/PageSavedListener.php
namespace App\EventListener;

use Aropixel\PageBundle\Event\PageSavedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: PageSavedEvent::NAME)]
class PageSavedListener
{
    public function __construct(private readonly \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient)
    {
    }

    public function __invoke(PageSavedEvent $event): void
    {
        $slug = $event->getPage()->getSlug();

        $this->httpClient->request('PURGE', 'https://your-varnish-host/' . $slug);
    }
}
```

#### Example: invalidate a Symfony Cache pool

```php
#[AsEventListener(event: PageSavedEvent::NAME)]
class PageSavedListener
{
    public function __construct(private readonly \Symfony\Contracts\Cache\TagAwareCacheInterface $cache)
    {
    }

    public function __invoke(PageSavedEvent $event): void
    {
        $this->cache->invalidateTags(['page_' . $event->getPage()->getId()]);
    }
}
```

### Replacing the builder's action menu

The builder's header carries a dropdown — save, new page, back to the list, preview. An application
that uses the builder for a single fixed page has no use for "new page" or "back to the list", so the
menu sits in its own Twig block:

```twig
{# templates/bundles/AropixelPageBundle/builder/index.html.twig #}
{% extends '@!AropixelPage/builder/index.html.twig' %}

{% block builder_actions %}
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="#" data-action="click->page-builder-saver#save">Enregistrer</a>
        </li>
    </ul>
{% endblock %}
```

Overriding it also frees the application from mounting the routes those entries point at
(`aropixel_builder_page`, `aropixel_page_index`): a route only has to exist if a rendered template
generates it.

### Overriding the builder's own URLs

The builder posts its saves to `aropixel_builder_page_save` and links the preview to
`aropixel_builder_page_preview`. An application whose admin carries context in the URL — a tenant id,
a locale — needs those URLs to carry it too, or the save lands without the context that identifies
the page's owner.

Three template variables override them, each defaulting to the bundle's own route:

```twig
{# templates/bundles/AropixelPageBundle/builder/index.html.twig #}
{% extends '@!AropixelPage/builder/index.html.twig' %}

{% set save_url = path('aropixel_builder_page_save', {tenant: app.request.get('tenant')}) %}
{% set preview_url = path('aropixel_builder_page_preview', {id: page.id, tenant: app.request.get('tenant')}) %}
{% set json_list_url = url('aropixel_builder_page_json_list', {tenant: app.request.get('tenant')}) %}
```

A `{% set %}` at the root of a child template is evaluated before the parent's blocks render, so the
variables reach them.

### Trimming the builder for a single fixed page

Besides `builder_actions` (above), two more blocks let an application drop what a fixed page does not
need:

| Block | What it holds |
|---|---|
| `builder_page_name` | The editable page name in the header — meaningless when the page is a fixed one |
| `builder_tabs` | The tab bar: page settings, inspector, content |

A footer, for instance, has no slug and no SEO metadata to set, and its name is not the author's to
choose:

```twig
{% block builder_page_name %}{% endblock %}

{% block builder_tabs %}
    <ul class="nav nav-tabs tab-underlined" id="myTab" role="tablist">
        <li class="nav-item"><a class="nav-link" href="#nav-structure" data-bs-toggle="tab" role="tab" id="nav-structure-tab">Inspecteur</a></li>
        <li class="nav-item"><a class="nav-link active" href="#nav-library" data-bs-toggle="tab" role="tab" id="nav-library-tab">Contenu</a></li>
    </ul>
{% endblock %}
```

Dropping a tab from the bar leaves its pane in the document, simply unreachable — harmless, and it
keeps the override to the bar itself.
