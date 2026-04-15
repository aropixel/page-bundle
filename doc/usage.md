# Usage and Page Types

## Page Types

The `AropixelPageBundle` supports two main page types:

### Default Page (`TYPE_DEFAULT`)
This is the standard page where content is stored as HTML. It's best suited for simple pages with a main text area using a WYSIWYG editor like CKEditor.
The property used for this type is `htmlContent`.

### Custom Page (`TYPE_CUSTOM`)
This type is designed for more complex layouts where content is stored as JSON. This is often used with a page builder or a blocks-based editor.
The property used for this type is `jsonContent`.

### Custom JSON Page Type
This type allows you to create structured forms whose data is stored as a JSON object in the `jsonContent` field. This is ideal for pages with specific fields (e.g., a "Contact" page with address and phone fields) without needing a full page builder.

To create a custom JSON page type:

1. **Extend `AbstractJsonPageType`**:
   ```php
   namespace App\Form\Type;

   use Aropixel\PageBundle\Form\Type\AbstractJsonPageType;
   use Symfony\Component\Form\FormBuilderInterface;
   use Symfony\Component\Form\Extension\Core\Type\TextType;

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

       /**
        * Optional: define a custom template for the form rendering.
        * By default, it looks for '@AropixelPage/contact/form.html.twig'.
        */
       public function getTemplate(): string
       {
           return '@App/admin/page/contact_form.html.twig';
       }
   }
   ```

2. **Create the form template**:
   By default, the bundle will look for a template in `@AropixelPage/{type}/form.html.twig`.
   You can provide it by creating a file in `templates/bundles/AropixelPageBundle/contact/form.html.twig`.

   Example of a custom form template:
   ```twig
   {# templates/bundles/AropixelPageBundle/contact/form.html.twig #}
   {% extends '@AropixelPage/default/form.html.twig' %}

   {% block mainPanel %}
       <div class="tab-pane active" id="panel-tab1">
           <div class="card card-centered-large">
               <div class="card-body">
                   {{ form_row(form.title) }}
                   <hr>
                   <div class="row">
                       <div class="col-md-6">{{ form_row(form.phone) }}</div>
                       <div class="col-md-6">{{ form_row(form.address) }}</div>
                   </div>
               </div>
           </div>
       </div>
       {{ parent() }}
   {% endblock %}
   ```

3. **Register it in your configuration**:
   ```yaml
   aropixel_page:
       forms:
           contact: App\Form\Type\ContactPageType
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
        {{ page.htmlContent|raw }}
    {% elseif page.type == 'custom' %}
        {# Render JSON content for custom builder #}
    {% else %}
        {# Render structured JSON content (e.g., for type 'contact') #}
        {% set data = page.jsonContent|json_decode %}
        <p>Phone: {{ data.phone }}</p>
        <p>Address: {{ data.address }}</p>
    {% endif %}
</div>
```
