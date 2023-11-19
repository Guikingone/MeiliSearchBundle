<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Form\Type;

use MeiliSearchBundle\Search\SearchEntryPointInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function array_merge;
use function is_array;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchChoiceType extends AbstractType
{
    private const INDEX = 'index';

    private const QUERY = 'query';

    private const ATTRIBUTES_TO_RETRIEVE = 'attributes_to_retrieve';

    private const ATTRIBUTE_TO_DISPLAY = 'attribute_to_display';

    public function __construct(private readonly SearchEntryPointInterface $searchEntryPoint)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            self::INDEX,
            self::ATTRIBUTE_TO_DISPLAY,
            self::ATTRIBUTES_TO_RETRIEVE,
            self::QUERY,
        ]);

        $resolver->setRequired([
            self::INDEX,
            self::ATTRIBUTE_TO_DISPLAY,
            self::QUERY,
        ]);

        $resolver->setDefaults([
            'choice_loader' => fn (Options $options): ChoiceLoaderInterface => new CallbackChoiceLoader(
                function () use ($options): array {
                    $result = $this->searchEntryPoint->search($options[self::INDEX], $options[self::QUERY], [
                        'attributesToRetrieve' => is_array(
                            $options[self::ATTRIBUTES_TO_RETRIEVE]
                        ) ? $options[self::ATTRIBUTES_TO_RETRIEVE] : [$options[self::ATTRIBUTES_TO_RETRIEVE]],
                    ]);

                    return array_merge(
                        ...array_map(
                            static fn (
                                array $hit
                            ): array => [$hit[$options[self::ATTRIBUTE_TO_DISPLAY]] => $hit[$options[self::ATTRIBUTE_TO_DISPLAY]]],
                            $result->getHits()
                        )
                    );
                }
            ),
            self::ATTRIBUTES_TO_RETRIEVE => '*',
        ]);

        $resolver->setAllowedTypes(self::INDEX, 'string');
        $resolver->setAllowedTypes(self::ATTRIBUTES_TO_RETRIEVE, ['string', 'array']);
        $resolver->setAllowedTypes(self::ATTRIBUTE_TO_DISPLAY, 'string');
        $resolver->setAllowedTypes(self::QUERY, 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'meilisearch_choice';
    }
}
