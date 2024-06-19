<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 05/07/2020 à 16:54
 */

namespace Aropixel\PageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


class PageFormResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('aropixel.page.form.factory');

        $taggedServices = $container->findTaggedServiceIds('aropixel.page.form');
        foreach ($taggedServices as $id => $tags) {

            $definition->addMethodCall('addForm', [new Reference($id)]);

        }
    }

}
