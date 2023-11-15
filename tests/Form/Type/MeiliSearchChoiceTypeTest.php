<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Form\Type;

use MeiliSearchBundle\Form\Type\MeiliSearchChoiceType;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResult;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchChoiceTypeTest extends TypeTestCase
{
    /**
     * @var SearchEntryPointInterface|MockObject
     */
    private $searchEntryPoint;

    /**
     * @return PreloadedExtension[]
     */
    protected function getExtensions(): array
    {
        $this->searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);

        return [
            new PreloadedExtension([
                new MeiliSearchChoiceType($this->searchEntryPoint),
            ], []),
        ];
    }

    public function testTypeCanBeResolved(): void
    {
        $type = new MeiliSearchChoiceType($this->searchEntryPoint);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects(self::once())->method('setDefined')->with([
            'index',
            'attribute_to_display',
            'attributes_to_retrieve',
            'query',
        ]);

        $type->configureOptions($resolver);
    }

    public function testTypeCanBeConfigured(): void
    {
        $type = new MeiliSearchChoiceType($this->searchEntryPoint);

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        static::assertSame(ChoiceType::class, $type->getParent());
        static::assertSame('meilisearch_choice', $type->getBlockPrefix());

        static::assertCount(5, $resolver->getDefinedOptions());
        static::assertContains('index', $resolver->getDefinedOptions());
        static::assertContains('attribute_to_display', $resolver->getDefinedOptions());
        static::assertContains('attributes_to_retrieve', $resolver->getDefinedOptions());
        static::assertContains('query', $resolver->getDefinedOptions());
        static::assertContains('choice_loader', $resolver->getDefinedOptions());

        static::assertContains('index', $resolver->getRequiredOptions());
        static::assertContains('attribute_to_display', $resolver->getRequiredOptions());
        static::assertContains('query', $resolver->getRequiredOptions());
    }

    public function testFormCannotBeCreatedWithInvalidIndexType(): void
    {
        static::expectException(InvalidOptionsException::class);
        static::expectExceptionCode(0);
        $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 123,
            'query' => 'bar',
            'attribute_to_display' => 'title',
            'attributes_to_retrieve' => ['id', 'title'],
        ]);
    }

    public function testFormCannotBeCreatedWithInvalidAttributeToDisplayType(): void
    {
        static::expectException(InvalidOptionsException::class);
        static::expectExceptionCode(0);
        $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 'foo',
            'query' => 'bar',
            'attribute_to_display' => false,
            'attributes_to_retrieve' => ['id', 'title'],
        ]);
    }

    public function testFormCannotBeCreatedWithInvalidAttributesToRetrieveType(): void
    {
        static::expectException(InvalidOptionsException::class);
        static::expectExceptionCode(0);
        $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 'foo',
            'query' => 'bar',
            'attribute_to_display' => 'title',
            'attributes_to_retrieve' => false,
        ]);
    }

    public function testFormCannotBeCreatedWithInvalidQueryType(): void
    {
        static::expectException(InvalidOptionsException::class);
        static::expectExceptionCode(0);
        $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 'foo',
            'query' => false,
            'attribute_to_display' => 'title',
            'attributes_to_retrieve' => ['id', 'title'],
        ]);
    }

    public function testFormCannotBeSubmittedWithUndefinedChoice(): void
    {
        $this->searchEntryPoint->expects(self::once())->method('search')->willReturn(
            SearchResult::create([
                [
                    'id' => 1,
                    'title' => 'bar',
                ],
                [
                    'id' => 2,
                    'title' => 'foo',
                ],
            ], 0, 20, 1, false, 1, 'bar')
        );

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
        $this->searchEntryPoint->expects(self::once())->method('search')
            ->with(
                self::equalTo('foo'),
                self::equalTo('bar'),
                self::equalTo(['attributesToRetrieve' => ['id', 'title']])
            )
            ->willReturn(
                SearchResult::create([
                    [
                        'id' => 1,
                        'title' => 'bar',
                    ],
                    [
                        'id' => 2,
                        'title' => 'foo',
                    ],
                ], 0, 20, 1, false, 1, 'bar')
            );

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

    public function testFormCanBeSubmittedWithValidChoiceAndSingleAttributeToRetrieve(): void
    {
        $this->searchEntryPoint->expects(self::once())->method('search')
            ->with(self::equalTo('foo'), self::equalTo('bar'), self::equalTo(['attributesToRetrieve' => ['title']]))
            ->willReturn(
                SearchResult::create([
                    [
                        'id' => 1,
                        'title' => 'bar',
                    ],
                    [
                        'id' => 2,
                        'title' => 'foo',
                    ],
                ], 0, 20, 1, false, 1, 'bar')
            );

        $form = $this->factory->create(MeiliSearchChoiceType::class, null, [
            'index' => 'foo',
            'query' => 'bar',
            'attribute_to_display' => 'title',
            'attributes_to_retrieve' => 'title',
        ]);

        $config = $form->getConfig();

        static::assertSame('foo', $config->getOption('index'));
        static::assertSame('bar', $config->getOption('query'));
        static::assertSame('title', $config->getOption('attribute_to_display'));
        static::assertSame('title', $config->getOption('attributes_to_retrieve'));
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
