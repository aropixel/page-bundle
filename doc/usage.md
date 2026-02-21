# Usage and Page Types

## Page Types

The `AropixelPageBundle` supports two main page types:

### Default Page (`TYPE_DEFAULT`)
This is the standard page where content is stored as HTML. It's best suited for simple pages with a main text area using a WYSIWYG editor like CKEditor.
The property used for this type is `htmlContent`.

### Custom Page (`TYPE_CUSTOM`)
This type is designed for more complex layouts where content is stored as JSON. This is often used with a page builder or a blocks-based editor.
The property used for this type is `jsonContent`.

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
    {% else %}
        {# Render JSON content for custom builder #}
    {% endif %}
</div>
```
