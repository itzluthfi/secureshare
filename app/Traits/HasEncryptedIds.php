<?php

namespace App\Traits;

use App\Services\IdEncryptionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasEncryptedIds
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->encrypted_id;
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If the field is explicitly specified (e.g. 'slug'), use default behavior
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        // Attempt to decrypt the ID
        $service = app(IdEncryptionService::class);
        $decryptedId = $service->decrypt($value);

        if (!$decryptedId) {
            throw (new ModelNotFoundException)->setModel(get_class($this), $value);
        }

        return $this->where($this->getRouteKeyName(), $decryptedId)->first();
    }

    /**
     * Get the encrypted ID attribute.
     *
     * @return string
     */
    public function getEncryptedIdAttribute()
    {
        $service = app(IdEncryptionService::class);
        return $service->encrypt($this->getKey());
    }
}
