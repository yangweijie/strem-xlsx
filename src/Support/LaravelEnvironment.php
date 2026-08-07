<?php
declare(strict_types=1);
 
namespace StreamXlsx\Support;
 
final class LaravelEnvironment
{
    public static function isBooted(): bool
    {
        return function_exists('app') && app()->bound('config');
    }
}
