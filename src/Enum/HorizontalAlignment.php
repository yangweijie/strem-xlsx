<?php
declare(strict_types=1);
 
namespace StreamXlsx\Enum;
 
final class HorizontalAlignment
{
    public const LEFT   = 'left';
    public const CENTER = 'center';
    public const RIGHT  = 'right';
 
    public static function from(string $value): string
    {
        if ($value === 'left' || $value === 'center' || $value === 'right') {
            return $value;
        }
        throw new \InvalidArgumentException(sprintf('Invalid alignment "%s".', $value));
    }
}
