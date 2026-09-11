<?php

namespace Aropixel\PageBundle\Component\Builder;

/**
 * Which block types authors may use, and which they may not do without — from
 * `page_builder.allowed_blocks` and `page_builder.required_blocks`.
 *
 * The library only renders allowed blocks, but that is presentation: a payload reaches the server as
 * JSON and nothing stops it carrying a block the project never offered, or dropping one it cannot do
 * without. This is where both lists are actually enforced.
 *
 * A required block answers a need the editor cannot be trusted to remember: a footer whose statutory
 * links have been deleted is not a matter of taste.
 */
class BlockPolicy
{
    /**
     * @param list<string> $allowedBlocks  empty means every type is allowed
     * @param list<string> $requiredBlocks types a payload must carry at least once; empty requires none
     */
    public function __construct(
        private readonly array $allowedBlocks = [],
        private readonly array $requiredBlocks = [],
    ) {
    }

    public function isAllowed(string $type): bool
    {
        return [] === $this->allowedBlocks || \in_array($type, $this->allowedBlocks, true);
    }

    /**
     * Block types present in a builder payload that the project does not allow.
     *
     * Walks sections, rows, columns and blocks, including the rows nested inside `nested-row`
     * blocks.
     *
     * @param array<mixed>|string|null $content The payload, as stored in `jsonContent`
     *
     * @return list<string> Offending types, each listed once, in encounter order
     */
    public function findForbidden(array|string|null $content): array
    {
        if ([] === $this->allowedBlocks) {
            return [];
        }

        $forbidden = array_filter(
            $this->collectTypes($content),
            fn (string $type): bool => !$this->isAllowed($type),
        );

        return array_values($forbidden);
    }

    /**
     * Required block types a builder payload does not carry.
     *
     * @param array<mixed>|string|null $content The payload, as stored in `jsonContent`
     *
     * @return list<string> Missing types, in the order they were configured
     */
    public function findMissing(array|string|null $content): array
    {
        if ([] === $this->requiredBlocks) {
            return [];
        }

        $present = $this->collectTypes($content);

        return array_values(array_diff($this->requiredBlocks, $present));
    }

    /**
     * Every block type a payload carries, each listed once, in encounter order.
     *
     * Walks sections, rows, columns and blocks, including the rows nested inside `nested-row` blocks
     * — one level of nesting would otherwise hide a block from both lists.
     *
     * @param array<mixed>|string|null $content
     *
     * @return list<string>
     */
    private function collectTypes(array|string|null $content): array
    {
        if (\is_string($content)) {
            $content = json_decode($content, true);
        }

        if (!\is_array($content)) {
            return [];
        }

        $sections = $content['sections'] ?? $content;
        $types = [];

        foreach (\is_array($sections) ? $sections : [] as $section) {
            $this->collectFromRows($section['rows'] ?? [], $types);
        }

        return array_values(array_unique($types));
    }

    /**
     * @param list<string> $types
     */
    private function collectFromRows(mixed $rows, array &$types): void
    {
        foreach (\is_array($rows) ? $rows : [] as $row) {
            foreach (\is_array($row['columns'] ?? null) ? $row['columns'] : [] as $column) {
                foreach (\is_array($column['blocks'] ?? null) ? $column['blocks'] : [] as $block) {
                    $type = $block['type'] ?? null;

                    if (!\is_string($type)) {
                        continue;
                    }

                    $types[] = $type;

                    // A nested row carries its own columns and blocks.
                    if (isset($block['row'])) {
                        $this->collectFromRows([$block['row']], $types);
                    }
                }
            }
        }
    }
}
