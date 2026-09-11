<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Entity\PageTranslation;
use Aropixel\PageBundle\Entity\PageTranslationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('aropixel_page');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('entities')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(PageInterface::class)->defaultValue(Page::class)->end()
                        ->scalarNode(PageTranslationInterface::class)->defaultValue(PageTranslation::class)->end()
                    ->end()
                ->end()
                ->arrayNode('page_builder')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->arrayNode('title_styles')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('value')->isRequired()->end()
                                    ->scalarNode('label')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('button_colors')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('value')->isRequired()->end()
                                    ->scalarNode('label')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('custom_blocks')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('type')->isRequired()->end()
                                    ->scalarNode('label')->isRequired()->end()
                                    ->scalarNode('icon')->defaultValue('lucide:puzzle')->end()
                                    ->scalarNode('category')->defaultValue('custom')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('allowed_blocks')
                            ->info('Block types authors may use. Empty (the default) allows them all.')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('required_blocks')
                            ->info('Block types a page must carry to be saved. Empty (the default) requires none.')
                            ->scalarPrototype()->end()
                        ->end()
                        ->scalarNode('custom_css')->defaultNull()->end()
                        ->arrayNode('front_route')
                            ->addDefaultsIfNotSet()
                            ->info('Route used to link to another page from a button or a clickable column.')
                            ->children()
                                ->scalarNode('name')
                                    ->defaultValue('front_page_show')
                                    ->info('Route name, defined by the application.')
                                ->end()
                                ->scalarNode('parameter')
                                    ->defaultValue('fullPath')
                                    ->info('Route parameter receiving the page path.')
                                ->end()
                                ->booleanNode('include_parent')
                                    ->defaultTrue()
                                    ->info('Prefix the page slug with its parent slug: true for hierarchical URLs (/{fullPath}), false for flat ones (/page/{slug}).')
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('renderer')
                            ->defaultValue('uikit')
                            ->validate()
                                ->ifNotInArray(['uikit', 'bootstrap'])
                                ->thenInvalid('Le renderer "%s" n\'est pas supporté. Valeurs acceptées : "uikit", "bootstrap".')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
