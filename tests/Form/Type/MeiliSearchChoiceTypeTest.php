<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Form\Type;

use MeiliSearchBundle\Form\Type\MeiliSearchChoiceType;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResult;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchChoiceTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $searchEntryPoint->expects(self::once())->method('search')->willReturn(SearchResult::create([
            [
                'id' => 1,
                'title' => 'bar',
            ],
            [
                'id' => 2,
                'title' => 'foo',
            ],
        ], 0, 20, 1, false, 30, 'bar'));

        return [
            new PreloadedExtension([
                new MeiliSearchChoiceType($searchEntryPoint)
            ], []),
        ];
    }

    public function testFormCannotBeSubmittedWithUndefinedChoice(): void
    {
        $form = $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 'foo',
            'query' => 'bar',
            'attribute_to_display' => 'title',
        ]);
        $form->submit('test');

        static::assertFalse($form->isValid());
    }

    public function testFormCanBeSubmittedWithValidChoice(): void
    {
        $form = $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 'foo',
            'query' => 'bar',
            'attribute_to_display' => 'title',
        ]);
        $form->submit('foo');

        static::assertTrue($form->isValid());
    }
}
