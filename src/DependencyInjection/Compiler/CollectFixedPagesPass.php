<?php

namespace Aropixel\PageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CollectFixedPagesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $fixedPages = [];

        foreach ($container->findTaggedServiceIds('aropixel_page.fixed_page') as $tags) {
            foreach ($tags as $tag) {
                $fixedPages[$tag['code']] = [
                    'title'     => $tag['title'],
                    'type'      => $tag['type'],
                    'deletable' => $tag['deletable'],
                ];
            }
        }

        $container->setParameter('aropixel_page.fixed_pages', $fixedPages);
    }
}
