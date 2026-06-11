<?php

declare(strict_types=1);

namespace App\Service\Admin;

interface DuplicatableInterface
{
    /**
     * Called on the cloned entity before it is persisted.
     * Use this to reset unique fields, clear owned collections, or nullify file uploads.
     */
    public function prepareDuplicate(): void;
}
