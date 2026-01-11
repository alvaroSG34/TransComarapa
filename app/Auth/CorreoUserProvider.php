<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CorreoUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            array_key_exists('password', $credentials))) {
            return;
        }

        // Build the query.
        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            // Reemplazar 'email' por 'correo' para búsquedas
            if ($key === 'email') {
                $query->where('correo', $value);
            } elseif (! str_contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}
