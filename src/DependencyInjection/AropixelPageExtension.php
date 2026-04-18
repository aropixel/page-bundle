<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Attribute\AsFixedPage;
use Aropixel\PageBundle\Component\Builder\BootstrapPageBuilderRenderer;
use Aropixel\PageBundle\Component\Builder\UiKitPageBuilderRenderer;
use Aropixel\PageBundle\Component\Builder\PageBuilderRendererInterface;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Aropixel\PageBundle\Form\Type\DefaultTranslatablePageType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
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

        $container->registerAttributeForAutoconfiguration(
            AsFixedPage::class,
            static function (ChildDefinition $definition, AsFixedPage $attribute): void {
                $definition->addTag('aropixel_page.fixed_page', [
                    'code'      => $attribute->code,
                    'title'     => $attribute->title,
                    'type'      => $attribute->type,
                    'deletable' => $attribute->deletable,
                ]);
            }
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $rendererKey = $config['page_builder']['renderer'] ?? 'uikit';
        $concreteClass = $rendererKey === 'bootstrap'
            ? BootstrapPageBuilderRenderer::class
            : UiKitPageBuilderRenderer::class;
        $container->setAlias(PageBuilderRendererInterface::class, new Alias($concreteClass, true));
    }

    /**
     * @param array<array<mixed>> $config
     */
    private function registerParameters(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('aropixel_page.entities', $config['entities']);
        $container->setParameter('aropixel_page.entities.page', $config['entities'][PageInterface::class]);
        $container->setParameter('aropixel_page.form.default', DefaultPageType::class);
        $container->setParameter('aropixel_page.form.default_translatable', DefaultTranslatablePageType::class);
        // Built-in types are hardcoded; RegisterPageFormTypesPass adds application types.
        $container->setParameter('aropixel_page.forms', [
            'default' => DefaultPageType::class,
            'default_translatable' => DefaultTranslatablePageType::class,
        ]);
        $locales = $container->hasParameter('aropixel_admin.locales')
            ? $container->getParameter('aropixel_admin.locales')
            : [];
        $container->setParameter('aropixel_page.page_builder', array_merge($config['page_builder'], ['locales' => $locales]));
        $container->setParameter('aropixel_page.page_builder.enabled', $config['page_builder']['enabled']);
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

        if (isset($bundles['LiipImagineBundle'])) {
            $container->prependExtensionConfig('liip_imagine', [
                'filter_sets' => [
                    'page' => [
                        'jpeg_quality' => 85,
                        'png_compression_level' => 8,
                        'filters' => [
                            'strip' => null,
                            'relative_resize' => ['widen' => 800],
                        ],
                    ],
                ],
            ]);
        }
    }
}
