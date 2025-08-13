<?php

namespace App\Models\Passport;

use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'redirect_uris' => 'array',
        'grant_types'   => 'array',
        'revoked'       => 'boolean',
    ];

    /**
     * Get the redirect URIs for the client.
     *
     * @return array<string>
     */
    public function getRedirectAttribute(mixed $value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [$value];
        }

        return $value ?: [];
    }

    /**
     * Set the redirect URIs for the client.
     */
    public function setRedirectAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['redirect'] = json_encode($value);
        } else {
            $this->attributes['redirect'] = json_encode([$value]);
        }
    }

    /**
     * Get the grant types for the client.
     *
     * @return array<string>
     */
    public function getGrantTypesAttribute(mixed $value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [$value];
        }

        return $value ?: [];
    }

    /**
     * Set the grant types for the client.
     */
    public function setGrantTypesAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['grant_types'] = json_encode($value);
        } else {
            $this->attributes['grant_types'] = json_encode([$value]);
        }
    }
}
