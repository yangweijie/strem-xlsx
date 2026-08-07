<?php
declare(strict_types=1);
 
namespace StreamXlsx\Builder;
 
use StreamXlsx\Dto\HeaderCell;
use StreamXlsx\Dto\HeaderDefinition;
use StreamXlsx\Exception\InvalidHeaderDefinitionException;
 
/**
 * Turns a raw header array (flat, multi-row, grouped or nested) into an
 * immutable HeaderDefinition tree with colspan/rowspan already resolved.
 *
 * Flat header:      ['No', 'Nama', 'Email']
 * Grouped header:   ['No', 'Nama', 'Kontak' => ['Email', 'Telepon']]
 * Nested (any depth): groups may contain groups recursively.
 */
final class HeaderBuilder
{
    public function build(array $definition): HeaderDefinition
    {
        if ($definition === []) {
            throw new InvalidHeaderDefinitionException('Header definition cannot be empty.');
        }
 
        $cells = $this->buildCells($definition);
        $depth = $this->maxDepth($cells);
        $cells = $this->normalizeRowspan($cells, $depth);
        $columnCount = array_sum(array_map(static function (HeaderCell $c): int {
            return $c->colspan;
        }, $cells));
 
        return new HeaderDefinition($cells, $depth, $columnCount);
    }
 
    /**
     * @return HeaderCell[]
     */
    private function buildCells(array $definition): array
    {
        $cells = [];
 
        foreach ($definition as $key => $value) {
            if (is_array($value)) {
                if (!is_string($key)) {
                    throw new InvalidHeaderDefinitionException(
                        'A group header must have a string label as its key.'
                    );
                }
                $children = $this->buildCells($value);
                $colspan = array_sum(array_map(static function (HeaderCell $c): int {
                    return $c->colspan;
                }, $children));
                $cells[] = new HeaderCell($key, $colspan, 1, $children);
                continue;
            }
            $cells[] = new HeaderCell((string) $value, 1, 1, []);
        }
 
        return $cells;
    }
 
    /**
     * @param HeaderCell[] $cells
     */
    private function maxDepth(array $cells): int
    {
        $depth = 1;
        foreach ($cells as $cell) {
            if ($cell->children !== []) {
                $depth = max($depth, 1 + $this->maxDepth($cell->children));
            }
        }
        return $depth;
    }
 
    /**
     * @param HeaderCell[] $cells
     * @return HeaderCell[]
     */
    private function normalizeRowspan(array $cells, int $totalDepth): array
    {
        $result = [];
        foreach ($cells as $cell) {
            if ($cell->children === []) {
                $result[] = new HeaderCell($cell->label, $cell->colspan, $totalDepth, []);
                continue;
            }
            $result[] = new HeaderCell(
                $cell->label,
                $cell->colspan,
                1,
                $this->normalizeRowspan($cell->children, $totalDepth - 1)
            );
        }
        return $result;
    }
}
