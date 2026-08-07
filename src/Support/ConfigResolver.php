<?php
declare(strict_types=1);
 
namespace StreamXlsx\Support;
 
use StreamXlsx\Contract\ConfigRepositoryInterface;
 
final class ConfigResolver
{
    public static function default(): ConfigRepositoryInterface
    {
        if (LaravelEnvironment::isBooted()) {
            return new LaravelConfigRepository();
        }
        return new ArrayConfigRepository([
            'creator' => 'StreamXlsx',
            'author'  => 'StreamXlsx',
            'company' => '',
        ]);
    }
}
