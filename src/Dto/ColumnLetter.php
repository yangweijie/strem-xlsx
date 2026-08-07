<?php
declare(strict_types=1);
 
namespace StreamXlsx\Helper;
 
final class ColumnLetter
{
    public static function fromIndex(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letter = chr(65 + $remainder) . $letter;
            $index = intdiv($index - $remainder, 26);
        }
        return $letter;
    }
 
    public static function toIndex(string $letter): int
    {
        $letter = strtoupper($letter);
        $index = 0;
        for ($i = 0, $length = strlen($letter); $i < $length; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - 64);
        }
        return $index;
    }
}
