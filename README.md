<p align="center">
  <a href="http://www.aropixel.com/">
    <img src="https://avatars1.githubusercontent.com/u/14820816?s=200&v=4" alt="Aropixel logo" width="75" height="75" style="border-radius:100px">
  </a>
</p>


<h1 align="center">Aropixel Page Bundle</h1>

<p>
  Aropixel Page Bundle is a complementray bundle of <a href="https://github.com/aropixel/admin-bundle">Aropixel Admin Bundle</a>. It gives possibility to manage standard pages for your website.   
</p>


![GitHub last commit](https://img.shields.io/github/last-commit/aropixel/page-bundle.svg)
[![GitHub issues](https://img.shields.io/github/issues/aropixel/page-bundle.svg)](https://github.com/stisla/stisla/issues)
[![License](https://img.shields.io/github/license/aropixel/page-bundle.svg)](LICENSE)

![Aropixel Page Preview](./screenshot-1.png)

![Aropixel Page Preview](./screenshot-2.png)


## Table of contents

- [Quick start](#quick-start)
- [License](#license)


## Quick start

- Create your symfony 4 project & install Aropixel AdminBundle
- Require Aropixel Page Bundle : `composer require aropixel/page-bundle`
- Apply migrations
- Include the routes :

```
aropixel_page:
  resource: '@AropixelPageBundle/Resources/config/routing.yml'
  prefix:   /admin
```

- create a ConfigureMenuListener class, register it as an event listener and include the page menu in the listener:

````
    App\EventListener\ConfigureMenuListener:
        tags:
            - { name: kernel.event_listener, event: aropixel.admin_menu_configure, method: onMenuConfigure }
````


````
<?php

declare(strict_types=1);

// src/AppBundle/EventListener/ConfigureMenuListener.php

namespace App\EventListener;

use Aropixel\AdminBundle\Event\ConfigureMenuEvent;
use Aropixel\AdminBundle\Menu\AbstractMenuListener;

class ConfigureMenuListener extends AbstractMenuListener
{
    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->factory = $event->getFactory();
        $this->em = $event->getEntityManager();
        $this->routeName = $request->get('_route');
        $this->routeParameters = $request->get('_route_params');

        $this->menu = $event->getAppMenu('main');
        if (!$this->menu) {
            $this->menu = $this->createRoot();
        }

        $pageMenu = [
            'route' => 'aropixel_page_index',
            'routeParameters' => [
                'type' => 'default'
            ]
        ];

        $this->addItem('Pages', $pageMenu, 'far fa-file');
        
        $event->addAppMenu($this->menu, false, 'main');
    }
}

````

## License
Aropixel Page Bundle is under the [MIT License](LICENSE)
