<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataProvider;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentDataProviderInterface
{
    /**
     * Return the index name used to link the document.
     */
    public function support(): string;

    /**
     * The returned array MUST respect the following structure:
     *
     * ```php
     * return [
     *     'key' => value,
     *     'key' => value,
     *     // ...
     * ];
     * ```
     *
     * In the case of an {@see EmbeddedDocumentDataProviderInterface}, the following structure must be set:
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
     * The returned array CAN contain multiple documents (only if {@see EmbeddedDocumentDataProviderInterface}).
     *
     * @return array<int|string, mixed>
     */
    public function getDocument(): array;
}
