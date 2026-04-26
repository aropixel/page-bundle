<?php

namespace Aropixel\PageBundle\Tests\Integration\Component\Builder;

use Aropixel\PageBundle\Component\Builder\PageBuilderRendererInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BootstrapPageBuilderRendererIntegrationTest extends KernelTestCase
{
    public function testServiceIsResolvableFromContainer(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $renderer = $container->get(PageBuilderRendererInterface::class);

        $this->assertInstanceOf(PageBuilderRendererInterface::class, $renderer);
    }

    public function testRenderMultiSectionPayloadReturnsValidHtml(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var PageBuilderRendererInterface $renderer */
        $renderer = $container->get(PageBuilderRendererInterface::class);

        $payload = [
            'sections' => [
                [
                    'background' => null,
                    'layout' => 'container',
                    'visibleDesktop' => true,
                    'visibleMobile' => true,
                    'rows' => [
                        [
                            'slider' => false,
                            'type' => 'default',
                            'align' => null,
                            'justify' => null,
                            'columns' => [
                                [
                                    'widths' => ['s' => '1-1'],
                                    'align' => null,
                                    'link' => null,
                                    'blocks' => [
                                        ['type' => 'title', 'content' => 'Section 1', 'size' => 'h2'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'background' => null,
                    'layout' => 'container',
                    'visibleDesktop' => true,
                    'visibleMobile' => true,
                    'rows' => [
                        [
                            'slider' => false,
                            'type' => 'default',
                            'align' => null,
                            'justify' => null,
                            'columns' => [
                                [
                                    'widths' => ['s' => '1-2'],
                                    'align' => null,
                                    'link' => null,
                                    'blocks' => [
                                        ['type' => 'text', 'content' => '<p>Contenu section 2</p>'],
                                    ],
                                ],
                                [
                                    'widths' => ['s' => '1-2'],
                                    'align' => null,
                                    'link' => null,
                                    'blocks' => [
                                        ['type' => 'divider'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $renderer->render($payload);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('<section', $result);
    }
}
