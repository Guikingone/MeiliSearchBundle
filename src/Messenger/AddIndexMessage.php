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
     * @var array<mixed,mixed>
     */
    private readonly array $configuration;

    public function __construct(
        private readonly string $uid,
        private readonly ?string $primaryKey = null,
        array $configuration = []
    ) {
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
            'distinctAttribute' => null,
            'facetedAttributes' => [],
            'searchableAttributes' => [],
            'displayedAttributes' => [],
            'rankingRules' => [],
            'stopWords' => [],
            'synonyms' => [],
        ]);
    }
}
