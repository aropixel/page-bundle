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
   }
   ```

2. **Register it in your configuration**:
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
