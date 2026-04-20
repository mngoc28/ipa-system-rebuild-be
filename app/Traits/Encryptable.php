<?php

namespace App\Traits;

/**
 * Trait Encryptable
 *
 * Automatically encrypts and decrypts specific Eloquent attributes
 * defined in the $encryptable property of the model.
 *
 * @package App\Traits
 */
trait Encryptable
{
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable, true)) {
            $value = !empty($value) ? cryptDecrypt($value) : null;
            return $value;
        }
        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable, true)) {
            $value = !empty($value) ? cryptEncrypt($value) : null;
        }
        return parent::setAttribute($key, $value);
    }

    public function attributesToArray()
    {
        // call the parent method
        $attributes = parent::attributesToArray();

        foreach ($this->encryptable as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = cryptDecrypt($attributes[$key]);
            }
        }
        return $attributes;
    }
}
