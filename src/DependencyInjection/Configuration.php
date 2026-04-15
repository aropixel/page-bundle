<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Entity\PageTranslation;
use Aropixel\PageBundle\Entity\PageTranslationInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Aropixel\PageBundle\Form\Type\DefaultTranslatablePageType;
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
                ->arrayNode('forms')
                    ->defaultValue([
                        'default' => ['class' => DefaultPageType::class],
                        'default_translatable' => ['class' => DefaultTranslatablePageType::class],
                    ])
                    ->useAttributeAsKey('type')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) { return ['class' => $v]; })
                        ->end()
                        ->children()
                            ->scalarNode('class')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('fixed_pages')
                    ->useAttributeAsKey('code')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('title')->isRequired()->end()
                            ->scalarNode('type')->defaultValue('default')->end()
                            ->booleanNode('deletable')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('page_builder')
                    ->addDefaultsIfNotSet()
                    ->children()
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
                                    ->scalarNode('icon')->defaultValue('fas fa-puzzle-piece')->end()
                                    ->scalarNode('category')->defaultValue('custom')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('custom_css')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
