<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Entity\PageInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AropixelPageExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerParameters($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    /**
     * @param array<array<mixed>> $config
     */
    private function registerParameters(ContainerBuilder $container, array $config): void
    {
        $forms = [];
        foreach ($config['forms'] as $type => $formConfig) {
            $forms[$type] = $formConfig['class'];
        }

        $container->setParameter('aropixel_page.entities', $config['entities']);
        $container->setParameter('aropixel_page.entities.page', $config['entities'][PageInterface::class]);
        $container->setParameter('aropixel_page.form.default', $forms['default'] ?? null);
        $container->setParameter('aropixel_page.form.default_translatable', $forms['default_translatable'] ?? null);
        $container->setParameter('aropixel_page.fixed_pages', $config['fixed_pages']);
        $container->setParameter('aropixel_page.forms', $forms);
        $locales = $container->hasParameter('aropixel_admin.locales')
            ? $container->getParameter('aropixel_admin.locales')
            : [];
        $container->setParameter('aropixel_page.page_builder', array_merge($config['page_builder'], ['locales' => $locales]));
    }

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
                        __DIR__ . '/../../assets' => '@aropixel/page-bundle',
                    ],
                ],
            ]);
        }

        if (isset($bundles['StimulusBundle'])) {
            $container->prependExtensionConfig('stimulus', [
                'controller_paths' => [
                    __DIR__ . '/../../assets',
                ],
            ]);
        }
    }
}
