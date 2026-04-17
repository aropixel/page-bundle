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

### 1. Configure fixed pages
In `config/packages/aropixel_page.yaml`:
```yaml
aropixel_page:
    fixed_pages:
        homepage:
            title: "Home"
            type: "default"
            deletable: false
        contact:
            title: "Contact"
            type: "contact"
            deletable: false
```

### 2. Synchronize with the database
Run the following command to create or update the fixed pages:
```bash
php bin/console aropixel:page:sync-fixed
```

The `staticCode` will be set to the key you provided (e.g., `homepage`), allowing you to safely fetch the page in your code:
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

The title block can offer a dropdown of predefined CSS styles. Each style maps a `value` (used as a CSS class in your front-end) to a human-readable `label` displayed in the admin interface.

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
