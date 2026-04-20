<?php

namespace App\Traits;

/**
 * Trait EnumTrait
 *
 * Provides utility methods for PHP Enums used in the application.
 *
 * @package App\Traits
 */
trait EnumTrait
{
    public function equals($value): bool
    {
        return $this->value == $value;
    }
}
