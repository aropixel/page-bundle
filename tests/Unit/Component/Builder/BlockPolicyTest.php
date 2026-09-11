<?php

namespace Aropixel\PageBundle\Tests\Unit\Component\Builder;

use Aropixel\PageBundle\Component\Builder\BlockPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BlockPolicyTest extends TestCase
{
    public function testAnEmptyListAllowsEveryType(): void
    {
        $policy = new BlockPolicy();

        self::assertTrue($policy->isAllowed('iframe'));
        self::assertSame([], $policy->findForbidden(self::payload(['iframe', 'slider'])));
    }

    public function testItAllowsOnlyTheConfiguredTypes(): void
    {
        $policy = new BlockPolicy(['text', 'image']);

        self::assertTrue($policy->isAllowed('text'));
        self::assertFalse($policy->isAllowed('iframe'));
    }

    public function testItReportsForbiddenBlocksOfAPayload(): void
    {
        $policy = new BlockPolicy(['text', 'image']);

        self::assertSame(['iframe', 'slider'], $policy->findForbidden(self::payload(['text', 'iframe', 'image', 'slider'])));
    }

    public function testItReportsEachForbiddenTypeOnce(): void
    {
        $policy = new BlockPolicy(['text']);

        self::assertSame(['iframe'], $policy->findForbidden(self::payload(['iframe', 'text', 'iframe'])));
    }

    public function testItAcceptsAJsonString(): void
    {
        $policy = new BlockPolicy(['text']);

        self::assertSame(['iframe'], $policy->findForbidden(json_encode(self::payload(['iframe']))));
    }

    /**
     * A `nested-row` block carries its own row, whose blocks must be checked too — otherwise the
     * whitelist is bypassed by one level of nesting.
     */
    public function testItWalksNestedRows(): void
    {
        $policy = new BlockPolicy(['text', 'nested-row']);

        $payload = ['sections' => [[
            'rows' => [[
                'columns' => [[
                    'blocks' => [[
                        'type' => 'nested-row',
                        'row' => ['columns' => [['blocks' => [['type' => 'iframe']]]]],
                    ]],
                ]],
            ]],
        ]]];

        self::assertSame(['iframe'], $policy->findForbidden($payload));
    }

    public function testNothingIsRequiredByDefault(): void
    {
        self::assertSame([], (new BlockPolicy())->findMissing(self::payload(['text'])));
    }

    public function testItReportsARequiredBlockThePayloadDoesNotCarry(): void
    {
        $policy = new BlockPolicy([], ['legal-links']);

        self::assertSame(['legal-links'], $policy->findMissing(self::payload(['text', 'image'])));
        self::assertSame([], $policy->findMissing(self::payload(['text', 'legal-links'])));
    }

    /**
     * Un bloc obligatoire enfoui dans une ligne imbriquée est présent : le refuser demanderait à
     * l'auteur de le remonter sans raison.
     */
    public function testARequiredBlockCountsWhereverItSits(): void
    {
        $payload = ['sections' => [[
            'rows' => [[
                'columns' => [[
                    'blocks' => [[
                        'type' => 'nested-row',
                        'row' => ['columns' => [['blocks' => [['type' => 'legal-links']]]]],
                    ]],
                ]],
            ]],
        ]]];

        self::assertSame([], (new BlockPolicy([], ['legal-links']))->findMissing($payload));
    }

    public function testAnEmptyPayloadMissesEveryRequiredBlock(): void
    {
        self::assertSame(['legal-links'], (new BlockPolicy([], ['legal-links']))->findMissing(null));
    }

    /**
     * @param list<mixed>|string|null $content
     */
    #[DataProvider('provideItToleratesAnEmptyOrMalformedPayloadCases')]
    public function testItToleratesAnEmptyOrMalformedPayload(array|string|null $content): void
    {
        self::assertSame([], (new BlockPolicy(['text']))->findForbidden($content));
    }

    /**
     * @return iterable<string, array{array<mixed>|string|null}>
     */
    public static function provideItToleratesAnEmptyOrMalformedPayloadCases(): iterable
    {
        yield 'null' => [null];
        yield 'empty array' => [[]];
        yield 'not json' => ['pas du json'];
        yield 'no sections' => [['sections' => []]];
        yield 'rows missing' => [['sections' => [[]]]];
        yield 'blocks without type' => [['sections' => [['rows' => [['columns' => [['blocks' => [[]]]]]]]]]];
    }

    /**
     * @param list<string> $types
     *
     * @return array<mixed>
     */
    private static function payload(array $types): array
    {
        return ['sections' => [[
            'rows' => [[
                'columns' => [[
                    'blocks' => array_map(static fn (string $type): array => ['type' => $type], $types),
                ]],
            ]],
        ]]];
    }
}
