<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Form\Type;

use MeiliSearchBundle\Form\Type\MeiliSearchChoiceType;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResult;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchChoiceTypeTest extends TypeTestCase
{
    /**
     * @return PreloadedExtension[]
     */
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
        ], 0, 20, 1, false, 1, 'bar'));

        return [
            new PreloadedExtension([
                new MeiliSearchChoiceType($searchEntryPoint)
            ], []),
        ];
    }

    public function testTypeCanBeConfigured(): void
    {
        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);

        $type = new MeiliSearchChoiceType($searchEntryPoint);

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        static::assertContains('index', $resolver->getDefinedOptions());
        static::assertContains('attribute_to_display', $resolver->getDefinedOptions());
        static::assertContains('attributes_to_retrieve', $resolver->getDefinedOptions());
        static::assertContains('query', $resolver->getDefinedOptions());
        static::assertContains('choice_loader', $resolver->getDefinedOptions());
    }

    public function testFormCannotBeSubmittedWithUndefinedChoice(): void
    {
        $form = $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 'foo',
            'query' => 'bar',
            'attribute_to_display' => 'title',
            'attributes_to_retrieve' => ['id', 'title'],
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
            'attributes_to_retrieve' => ['id', 'title'],
        ]);

        $config = $form->getConfig();

        static::assertSame('foo', $config->getOption('index'));
        static::assertSame('bar', $config->getOption('query'));
        static::assertSame('title', $config->getOption('attribute_to_display'));
        static::assertSame(['id', 'title'], $config->getOption('attributes_to_retrieve'));
        static::assertInstanceOf(CallbackChoiceLoader::class, $config->getOption('choice_loader'));

        $choiceList = $config->getOption('choice_loader')->loadChoiceList();

        static::assertCount(2, $choiceList->getChoices());
        static::assertCount(2, $choiceList->getValues());
        static::assertCount(2, $choiceList->getStructuredValues());
        static::assertCount(2, $choiceList->getOriginalKeys());
        static::assertArrayHasKey('foo', $choiceList->getStructuredValues());
        static::assertArrayHasKey('bar', $choiceList->getStructuredValues());

        $form->submit('foo');

        static::assertTrue($form->isValid());

        $view = $form->createView();

        static::assertSame('foo', $view->vars['value']);
        static::assertSame('foo', $view->vars['data']);
        static::assertCount(2, $view->vars['choices']);
    }
}
