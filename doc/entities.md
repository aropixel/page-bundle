# Entity Customization

The `AropixelPageBundle` uses interfaces and mapped superclasses to allow for easy extension.

## Entities and Interfaces

| Interface | Mapped Superclass | Purpose |
| --- | --- | --- |
| `PageInterface` | `Page` | Stores the main page content and status. |
| `PageTranslationInterface` | `PageTranslation` | Stores the translatable content for a page. |

### Fields

| Field | Type | Purpose |
| --- | --- | --- |
| `id` | `int` | Unique identifier. |
| `type` | `string` | The type of the page (`default`, `custom`, etc.). |
| `status` | `string` | Publication status (`online`, `offline`). |
| `staticCode` | `string` | A unique code for system pages (e.g., `homepage`). |
| `isDeletable` | `boolean` | Whether the page can be deleted in the admin. |
| `title` | `string` | The page title (translatable). |
| `slug` | `string` | URL-friendly title (translatable). |
| `htmlContent` | `text` | Main HTML content (translatable). |
| `jsonContent` | `text` | Structured JSON content (translatable). |

## Customizing the `Page` Entity

To add new fields to the `Page` entity:

1. Create a custom `Page` entity in your application:

```php
// src/Entity/Page.php
namespace App\Entity;

use Aropixel\PageBundle\Entity\Page as BasePage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_page')]
class Page extends BasePage
{
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $customField = null;

    public function getCustomField(): ?string
    {
        return $this->customField;
    }

    public function setCustomField(?string $customField): self
    {
        $this->customField = $customField;
        return $this;
    }
}
```

2. Configure the bundle to use your entity:

```yaml
# config/packages/aropixel_page.yaml
aropixel_page:
    entities:
        Aropixel\PageBundle\Entity\PageInterface: App\Entity\Page
```

## Customizing the `PageTranslation` Entity

Similarly, to add translatable fields, you can extend the `PageTranslation` entity:

```php
// src/Entity/PageTranslation.php
namespace App\Entity;

use Aropixel\PageBundle\Entity\PageTranslation as BasePageTranslation;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_page_translation')]
class PageTranslation extends BasePageTranslation
{
    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;
}
```

Make sure to register it in `aropixel_page.yaml`:

```yaml
aropixel_page:
    entities:
        Aropixel\PageBundle\Entity\PageTranslationInterface: App\Entity\PageTranslation
```
