<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Entity\Field;
use Aropixel\PageBundle\Entity\FieldInterface;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('aropixel_page');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('entities')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(PageInterface::class)->defaultValue(Page::class)->end()
                        ->scalarNode(FieldInterface::class)->defaultValue(Field::class)->end()
                    ->end()
                ->end()
                ->arrayNode('forms')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default')->defaultValue(DefaultPageType::class)->end()
                        ->scalarNode('template_path')->defaultValue('@AropixelPage/page')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
