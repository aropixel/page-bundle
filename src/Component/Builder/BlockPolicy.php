<?php

namespace Aropixel\PageBundle\Component\Builder;

/**
 * Which block types authors may use, from `page_builder.allowed_blocks`.
 *
 * The library only renders allowed blocks, but that is presentation: a payload reaches the server as
 * JSON and nothing stops it carrying a block the project never offered. This is where the list is
 * actually enforced.
 */
class BlockPolicy
{
    /**
     * @param list<string> $allowedBlocks empty means every type is allowed
     */
    public function __construct(
        private readonly array $allowedBlocks = [],
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

        if (\is_string($content)) {
            $content = json_decode($content, true);
        }

        if (!\is_array($content)) {
            return [];
        }

        $sections = $content['sections'] ?? $content;
        $forbidden = [];

        foreach (\is_array($sections) ? $sections : [] as $section) {
            $this->collectFromRows($section['rows'] ?? [], $forbidden);
        }

        return array_values(array_unique($forbidden));
    }

    /**
     * @param list<string> $forbidden
     */
    private function collectFromRows(mixed $rows, array &$forbidden): void
    {
        foreach (\is_array($rows) ? $rows : [] as $row) {
            foreach (\is_array($row['columns'] ?? null) ? $row['columns'] : [] as $column) {
                foreach (\is_array($column['blocks'] ?? null) ? $column['blocks'] : [] as $block) {
                    $type = $block['type'] ?? null;

                    if (!\is_string($type)) {
                        continue;
                    }

                    if (!$this->isAllowed($type)) {
                        $forbidden[] = $type;
                    }

                    // A nested row carries its own columns and blocks.
                    if (isset($block['row'])) {
                        $this->collectFromRows([$block['row']], $forbidden);
                    }
                }
            }
        }
    }
}
