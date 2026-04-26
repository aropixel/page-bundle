<?php

namespace Aropixel\PageBundle\Tests\Integration\Form\Type;

use App\Form\Type\ContactPageType;
use Aropixel\PageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class AbstractJsonPageTypeTest extends KernelTestCase
{
    public function testContactPageTypeBuildsWithoutError(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $formFactory = $container->get(FormFactoryInterface::class);
        $page = new Page();
        $page->setType('contact');

        $form = $formFactory->create(ContactPageType::class, $page);

        $this->assertNotNull($form);
    }

    public function testContactPageTypeReturnsCorrectType(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $type = $container->get(ContactPageType::class);

        $this->assertSame('contact', $type->getType());
    }

    public function testContactPageTypeHasPhoneAndAddressFields(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $formFactory = $container->get(FormFactoryInterface::class);
        $page = new Page();
        $page->setType('contact');

        $form = $formFactory->create(ContactPageType::class, $page);

        $this->assertTrue($form->has('phone'));
        $this->assertTrue($form->has('address'));
    }
}
