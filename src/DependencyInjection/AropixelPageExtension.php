<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Entity\PageInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AropixelPageExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerParameters($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }


    /**
     * @param array<array<mixed>> $config
     */
    private function registerParameters(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('aropixel_page.entities', $config['entities']);
        $container->setParameter('aropixel_page.entities.page', $config['entities'][PageInterface::class]);
        $container->setParameter('aropixel_page.form.default', $config['forms']['default']);
        $container->setParameter('aropixel_page.form.default_translatable', $config['forms']['default_translatable']);
    }


    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        $config = array_merge(...$container->getExtensionConfig('doctrine'));

        // Automatically register the bundle's entities as PHP attributes.
        // This avoids having to manually configure the mapping in doctrine.yaml.
        if (!empty($config['dbal']) && !empty($config['orm'])) {
            $container->prependExtensionConfig('doctrine', ['orm' => ['mappings' => ['AropixelPageBundle' => ['is_bundle' => true, 'type' => 'attribute']]]]);
        }

        if (isset($bundles['FrameworkBundle'])) {
            $container->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        __DIR__.'/../../assets' => '@aropixel/page-bundle',
                    ],
                ],
            ]);
        }

        if (isset($bundles['StimulusBundle'])) {
            $container->prependExtensionConfig('stimulus', [
                'controller_paths' => [
                    __DIR__.'/../../assets',
                ],
            ]);
        }
    }
}
