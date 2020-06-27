<?php

declare(strict_types=1);

namespace MeiliSearchBundle\src\DataProvider;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentDataProviderInterface
{
    /**
     * Return the index name which is used to link the document.
     *
     * @return string
     */
    public function support(): string;

    /**
     * The returned array MUST respect the following structure:
     *
     * ```php
     * return [
     *     [
     *         'key' => value,
     *         'key' => value,
     *     ],
     *     // ...
     * ];
     * ```
     *
     * The inner arrays can have multiple attributs but they MUST have at least a primary key field, this primary key
     * CAN be overridden when loading the document into an index.
     *
     * The returned array CAN contain multiple documents.
     */
    public function getDocument(): array;

    /**
     * Define the primary key of the loaded document(s).
     *
     * If the method return `null`, the primary key is the field `id` (MUST be present in the document payload).
     *
     * @return string
     */
    public function getPrimaryKey(): ?string;
}
