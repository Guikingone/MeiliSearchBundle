<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddIndexMessage implements MessageInterface
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var string|null
     */
    private $primaryKey;

    /**
     * @var array<mixed,mixed>
     */
    private $configuration;

    public function __construct(
        string $uid,
        ?string $primaryKey = null,
        array $configuration = []
    ) {
        $this->uid = $uid;
        $this->primaryKey = $primaryKey;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->configuration = $resolver->resolve($configuration);
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'acceptNewFields' => false,
            'distinctAttribute' => null,
            'facetedAttributes' => [],
            'searchableAttributes' => [],
            'displayedAttributes' => [],
        ]);
    }
}
