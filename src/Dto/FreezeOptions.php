<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class FreezeOptions
{
    /** @var string */
    public $mode;
    /** @var string|int|null */
    public $value;
 
    public function __construct(string $mode, $value = null)
    {
        $this->mode  = $mode;
        $this->value = $value;
    }
}
