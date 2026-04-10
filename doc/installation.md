# Installation

## Prerequisites

- PHP 8.2 or higher.
- Symfony 6.4 or 7.x.
- `aropixel/admin-bundle` already installed and configured.

## Step 1: Install the Bundle

Run the following command in your terminal:

```bash
composer require aropixel/page-bundle
```

## Step 2: Configure Doctrine Mappings

The bundle uses PHP attributes for its Doctrine mapping. Ensure your `doctrine.yaml` is configured to scan the bundle entities or let the bundle's extension handle it (default behavior).

## Step 3: Configure Routes

Import the bundle's routes in your `config/routes.yaml`:

```yaml
aropixel_page:
    resource: "@AropixelPageBundle/src/Resources/config/routes.yaml"
    prefix: /admin/page
```

## Step 4: Create your Entities

You should create your own entities that extend the bundle's mapped superclasses:

```php
// src/Entity/Page.php
namespace App\Entity;

use Aropixel\PageBundle\Entity\Page as BasePage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_page')]
class Page extends BasePage
{
}
```

```php
// src/Entity/PageTranslation.php
namespace App\Entity;

use Aropixel\PageBundle\Entity\PageTranslation as BasePageTranslation;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_page_translation')]
class PageTranslation extends BasePageTranslation
{
}
```

## Step 5: Final Configuration

Update your `config/packages/aropixel_page.yaml` if you want to use your custom entities:

```yaml
aropixel_page:
    entities:
        Aropixel\PageBundle\Entity\PageInterface: App\Entity\Page
        Aropixel\PageBundle\Entity\PageTranslationInterface: App\Entity\PageTranslation
```

Then run the migrations:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```
