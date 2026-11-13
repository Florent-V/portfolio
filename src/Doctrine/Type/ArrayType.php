<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * Réimplémentation du type "array" supprimé en DBAL 4, requis par le mapping
 * Gedmo Loggable (ext_log_entries.data). Format de stockage identique au
 * type DBAL 3 (serialize/unserialize) pour conserver les logs existants.
 */
final class ArrayType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        return serialize($value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        $value = \is_resource($value) ? stream_get_contents($value) : $value;

        set_error_handler(static function (int $code, string $message): never {
            throw ValueNotConvertible::new($message, 'array');
        });

        try {
            return unserialize($value);
        } finally {
            restore_error_handler();
        }
    }
}
