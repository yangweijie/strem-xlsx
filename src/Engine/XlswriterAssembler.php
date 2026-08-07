<?php
declare(strict_types=1);
 
namespace StreamXlsx\Engine;
 
use StreamXlsx\Contract\ConfigRepositoryInterface;
use StreamXlsx\Dto\ExportResult;
use StreamXlsx\Dto\FreezeOptions;
use StreamXlsx\Dto\SheetDefinition;
use StreamXlsx\Dto\SpreadsheetDefinition;
use StreamXlsx\Dto\StyleOptions;
use StreamXlsx\Enum\FreezeMode;
use StreamXlsx\Factory\RowSourceAdapterFactory;
use StreamXlsx\Helper\ColumnLetter;
use StreamXlsx\Helper\RowExtractor;
use Vtiful\Kernel\Excel;
use Vtiful\Kernel\Format;
 
/**
 * Core assembler. Iterates SheetDefinition[] and writes directly to disk
 * via xlswriter C extension — no in-memory spreadsheet object.
 *
 * Memory usage is constant regardless of row count.
 */
final class XlswriterAssembler
{
    /** @var RowSourceAdapterFactory */
    private $rowAdapter;
 
    public function __construct(?RowSourceAdapterFactory $rowAdapter = null)
    {
        $this->rowAdapter = $rowAdapter ?? new RowSourceAdapterFactory();
    }
 
    public function assemble(
        SpreadsheetDefinition $definition,
        ConfigRepositoryInterface $config,
        string $filename,
        string $format = 'xlsx'
    ): ExportResult {
        $ext = $format === 'csv' ? '.csv' : '.xlsx';
        $mimeType = $format === 'csv'
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
 
        $tempPath = sys_get_temp_dir() . '/' . uniqid('stream_xlsx_', true) . $ext;
 
        $excel = new Excel(['path' => dirname($tempPath)]);
        $file  = $excel->fileName(basename($tempPath));
 
        if ($format === 'csv') {
            $file->setCSV();
        }
 
        $sheetIndex = 0;
 
        foreach ($definition->sheets as $sheetDef) {
            if ($sheetIndex > 0) {
                $file = $excel->addSheet($sheetDef->name);
            } else {
                $file = $file->setTitle($sheetDef->name);
            }
 
            $this->writeSheet($file, $sheetDef, $config);
            $sheetIndex++;
        }
 
        $file->output();
 
        return new ExportResult($tempPath, $filename, $mimeType);
    }
 
    private function writeSheet(
        \Vtiful\Kernel\Excel $file,
        SheetDefinition $def,
        ConfigRepositoryInterface $config
    ): void {
        $handle    = $file->getHandle();
        $cursorRow = 1;
 
        // ── 1. 标题块 ──
        if ($def->title !== null) {
            $titleFormat = (new Format($handle))->bold()->fontSize(14)->toResource();
            $file->insertText($cursorRow - 1, 0, $def->title, null, $titleFormat);
            $cursorRow++;
 
            if ($def->subtitle !== null) {
                $subFormat = (new Format($handle))->fontSize(11)->toResource();
                $file->insertText($cursorRow - 1, 0, $def->subtitle, null, $subFormat);
                $cursorRow++;
            }
 
            if ($def->description !== null) {
                $file->insertText($cursorRow - 1, 0, $def->description);
                $cursorRow++;
            }
 
            $cursorRow++;
        }
 
        // ── 2. 表头 ──
        $headerRowCount = 0;
 
        if ($def->header !== null) {
            $headerRowCount = $this->writeHeaders($file, $def, $handle, $cursorRow);
            $cursorRow += $headerRowCount;
        }
 
        $dataStartRow = $cursorRow;
 
        // ── 3. 数据行 ──
        $cursorRow = $this->writeRows($file, $def, $handle, $cursorRow);
        $dataEndRow = $cursorRow - 1;
 
        // ── 4. 冻结 ──
        if ($def->freeze !== null) {
            $this->applyFreeze($file, $def->freeze, $headerRowCount);
        }
 
        // ── 5. 筛选 ──
        if ($def->filter && $def->header !== null) {
            $lastCol = ColumnLetter::fromIndex(max($def->header->columnCount, 1));
            $file->autoFilter("A{$dataStartRow}:{$lastCol}{$dataEndRow}");
        }
 
        // ── 6. 列宽 ──
        $this->applyColumnWidths($file, $def);
 
        // ── 7. 合并 ──
        foreach ($def->merges as $range) {
            $file->merge($range, '');
        }
 
        // ── 8. 图片 ──
        foreach ($def->images as $image) {
            $coords = $this->cellToRowCol($image['cell']);
            $file->insertImage($coords[0], $coords[1], $image['path']);
        }
    }
 
    private function writeHeaders(
        \Vtiful\Kernel\Excel $file,
        SheetDefinition $def,
        $handle,
        int $startRow
    ): int {
        $headerDef   = $def->header;
        $headerStyle = $def->headerStyle ?? new StyleOptions(true);
        $format      = XlswriterFormatConverter::toFormat($headerStyle, $handle);
 
        $rowCount = $headerDef->rowCount;
        $flatRows = $this->flattenHeaderRows($headerDef->cells, $rowCount);
 
        $row = $startRow;
 
        foreach ($flatRows as $columns) {
            $colIndex = 0;
 
            foreach ($columns as $cell) {
                if ($cell['colspan'] > 1 || $cell['rowspan'] > 1) {
                    $startCol = ColumnLetter::fromIndex($colIndex + 1);
                    $endCol   = ColumnLetter::fromIndex($colIndex + $cell['colspan']);
                    $endRow   = $row + $cell['rowspan'] - 1;
                    $file->merge(
                        "{$startCol}{$row}:{$endCol}{$endRow}",
                        $cell['label']
                    );
                } else {
                    $file->insertText(
                        $row - 1,
                        $colIndex,
                        $cell['label'],
                        null,
                        $format
                    );
                }
 
                $colIndex += $cell['colspan'];
            }
 
            $row++;
        }
 
        return $rowCount;
    }
 
    /**
     * @return array<int, array<int, array{label: string, colspan: int, rowspan: int}>>
     */
    private function flattenHeaderRows(array $cells, int $rowCount): array
    {
        $rows = array_fill(0, $rowCount, []);
 
        foreach ($cells as $cell) {
            $rows[0][] = [
                'label'   => $cell->label,
                'colspan' => $cell->colspan,
                'rowspan' => $cell->rowspan,
            ];
 
            if (!empty($cell->children)) {
                $childRows = $this->flattenHeaderRows($cell->children, $rowCount - 1);
                foreach ($childRows as $i => $childRow) {
                    foreach ($childRow as $cr) {
                        $rows[$i + 1][] = $cr;
                    }
                }
            }
        }
 
        return $rows;
    }
 
    private function writeRows(
        \Vtiful\Kernel\Excel $file,
        SheetDefinition $def,
        $handle,
        int $startRow
    ): int {
        if ($def->rows === null) {
            return $startRow;
        }
 
        $row        = $startRow;
        $rowStyle   = $def->rowStyle;
        $bodyFormat = null;
        if ($def->bodyStyle !== null) {
            $bodyFormat = XlswriterFormatConverter::toFormat($def->bodyStyle, $handle);
        }
        $index = 0;
 
        foreach ($this->rowAdapter->toIterable($def->rows) as $item) {
            $values = RowExtractor::values($item);
 
            $format = $bodyFormat;
 
            if ($rowStyle !== null && $rowStyle->alternateColor !== null && $index % 2 === 1) {
                $altFormat = XlswriterFormatConverter::toAlternateFormat($rowStyle, $handle, false);
                if ($altFormat !== null) {
                    $format = $altFormat;
                }
            }
 
            foreach ($values as $colIndex => $value) {
                $colLetter = ColumnLetter::fromIndex($colIndex + 1);
 
                if (isset($def->columnFormats[$colLetter])) {
                    $value = $this->formatValue($def->columnFormats[$colLetter], $value);
                }
 
                $strValue = '';
                if (is_string($value) || is_numeric($value)) {
                    $strValue = (string) $value;
                }
 
                $file->insertText(
                    $row - 1,
                    $colIndex,
                    $strValue,
                    null,
                    $format
                );
            }
 
            if ($rowStyle !== null && $rowStyle->rowHeight !== null) {
                $file->setRow("A{$row}", $rowStyle->rowHeight);
            }
 
            $row++;
            $index++;
        }
 
        return $row;
    }
 
    private function applyFreeze(
        \Vtiful\Kernel\Excel $file,
        FreezeOptions $freeze,
        int $headerRowCount
    ): void {
        if ($freeze->mode === FreezeMode::HEADER) {
            $file->freeze($headerRowCount, 0);
            return;
        }
 
        if ($freeze->mode === FreezeMode::CELL) {
            $coords = $this->cellToRowCol((string) $freeze->value);
            $file->freeze($coords[0], $coords[1]);
            return;
        }
 
        if ($freeze->mode === FreezeMode::COLUMN) {
            $colIndex = ColumnLetter::toIndex($freeze->value) - 1;
            $file->freeze(0, $colIndex);
            return;
        }
 
        if ($freeze->mode === FreezeMode::ROW) {
            $file->freeze((int) $freeze->value, 0);
        }
    }
 
    private function applyColumnWidths(\Vtiful\Kernel\Excel $file, SheetDefinition $def): void
    {
        foreach ($def->columnWidths as $colLetter => $width) {
            $file->setColumn("{$colLetter}:{$colLetter}", $width);
        }
 
        if ($def->autoWidth && $def->header !== null) {
            $colCount = $def->header->columnCount;
            for ($i = 0; $i < $colCount; $i++) {
                $letter = ColumnLetter::fromIndex($i + 1);
                if (!isset($def->columnWidths[$letter])) {
                    $file->setColumn("{$letter}:{$letter}", 15);
                }
            }
        }
    }
 
    /**
     * @param string $format
     * @param mixed $value
     * @return mixed
     */
    private function formatValue(string $format, $value)
    {
        if ($format === 'date') {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            if (is_numeric($value)) {
                return date('Y-m-d', (int) $value);
            }
            return $value;
        }
 
        if ($format === 'datetime') {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            }
            if (is_numeric($value)) {
                return date('Y-m-d H:i:s', (int) $value);
            }
            return $value;
        }
 
        if ($format === 'currency') {
            if (is_numeric($value)) {
                return number_format((float) $value, 2);
            }
            return $value;
        }
 
        if ($format === 'percentage') {
            if (is_numeric($value)) {
                return round((float) $value * 100, 2) . '%';
            }
            return $value;
        }
 
        if ($format === 'number') {
            if (is_numeric($value)) {
                return number_format((float) $value);
            }
            return $value;
        }
 
        return $value;
    }
 
    /**
     * "B3" → [rowIndex=2, colIndex=1] (0-based for xlswriter).
     *
     * @param string $cell
     * @return array{0:int,1:int}
     */
    private function cellToRowCol(string $cell): array
    {
        preg_match('/([A-Z]+)(\d+)/', strtoupper($cell), $matches);
        $colIndex = 0;
        foreach (str_split($matches[1]) as $char) {
            $colIndex = $colIndex * 26 + (ord($char) - ord('A') + 1);
        }
        $colIndex--;
        $rowIndex = (int) $matches[2] - 1;
 
        return [$rowIndex, $colIndex];
    }
}
