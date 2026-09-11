<?php

namespace Aropixel\PageBundle\Tests\Unit\Component\Security;

use Aropixel\PageBundle\Component\Security\AllowAllPageAccessChecker;
use Aropixel\PageBundle\Component\Security\PageAccessCheckerInterface;
use Aropixel\PageBundle\Entity\PageInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AllowAllPageAccessCheckerTest extends TestCase
{
    /**
     * An application that does not replace the service must behave exactly as it did before the
     * checker existed.
     */
    #[DataProvider('provideItGrantsEveryAttributeCases')]
    public function testItGrantsEveryAttribute(string $attribute): void
    {
        $checker = new AllowAllPageAccessChecker();

        self::assertTrue($checker->isGranted($attribute, $this->createMock(PageInterface::class)));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideItGrantsEveryAttributeCases(): iterable
    {
        yield 'view' => [PageAccessCheckerInterface::VIEW];
        yield 'edit' => [PageAccessCheckerInterface::EDIT];
        yield 'delete' => [PageAccessCheckerInterface::DELETE];
    }
}
