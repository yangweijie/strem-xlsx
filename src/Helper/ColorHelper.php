<?php
declare(strict_types=1);

namespace StreamXlsx\Helper;

use StreamXlsx\Exception\InvalidConfigurationException;

final class ColorHelper
{
    public static function normalize(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            throw new InvalidConfigurationException(sprintf('Invalid hex color "%s".', $hex));
        }
        return strtoupper($hex);
    }

    public static function toInt(string $hex): int
    {
        return (int) hexdec(self::normalize($hex));
    }
}