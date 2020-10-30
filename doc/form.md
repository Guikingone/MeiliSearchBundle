# Form

This bundle provides a [MeiliSearchChoiceType](../src/Form/Type/MeiliSearchChoiceType.php)
that allows to display a dropdown filled with the result of a search in the MeiliSearch API.

Here's how to use it: 

```php
<?php

use MeiliSearchBundle\Form\Type\MeiliSearchChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,array $options)
    {
        $builder
            // Other fields
            ->add('posts', MeiliSearchChoiceType::class, [
                'index' => 'posts',
                'query' => 'bar',
                'attribute_to_display' => 'title',
            ])
        ;
    }
}
```

The important options are `index`, `query` and `attribute_to_display`, here's the role of each:

- **index**: Define the index where the document should be searched.

- **query**: Define the search that should be performed.

- **attribute_to_display**: Define the attribute that should be displayed.

_Note_:

- There's a fourth attribute `attributes_to_retrieve` that allow to filter the retrieved fields of each document.

- This form uses the `CallbackChoiceLoader`, this way, the form can query choices thanks to lazy-loading, 
more info on the [official documentation](https://symfony.com/doc/current/reference/forms/types/choice.html#choice-loader).
