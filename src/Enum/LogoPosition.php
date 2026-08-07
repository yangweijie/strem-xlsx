<?php
declare(strict_types=1);
 
namespace StreamXlsx\Enum;
 
final class LogoPosition
{
    public const LEFT   = 'left';
    public const RIGHT  = 'right';
    public const CENTER = 'center';
 
    public static function from(string $value): string
    {
        if ($value === 'left' || $value === 'right' || $value === 'center') {
            return $value;
        }
        throw new \InvalidArgumentException(sprintf('Invalid logo position "%s".', $value));
    }
}
