<?php
declare(strict_types=1);
 
namespace StreamXlsx\Helper;
 
final class RowExtractor
{
    /**
     * @param mixed $row
     * @return array
     */
    public static function values($row): array
    {
        if (is_array($row)) {
            return array_values($row);
        }
        if (is_object($row)) {
            if (method_exists($row, 'toArray')) {
                return array_values($row->toArray());
            }
            return array_values(get_object_vars($row));
        }
        return [$row];
    }
}
