<?php

namespace App\Exceptions;

use RuntimeException;

class JwtKeyException extends RuntimeException
{
    public static function missing(string $type): self
    {
        return new self(sprintf(
            'JWT %1$s key is not configured. Set the %2$s environment variable to a key file path, a raw PEM string, or a base64 encoded PEM string.',
            $type,
            self::envVariable($type),
        ));
    }

    public static function invalid(string $type): self
    {
        return new self(sprintf(
            'JWT %1$s key is invalid. The %2$s environment variable must contain a key file path, a raw PEM string, or a base64 encoded PEM string.',
            $type,
            self::envVariable($type),
        ));
    }

    private static function envVariable(string $type): string
    {
        return $type === 'public' ? 'JWT_PUBLIC_KEY' : 'JWT_PRIVATE_KEY';
    }
}
