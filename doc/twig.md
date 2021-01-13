# Twig

This bundle provides a [SearchExtension](../src/Twig/SearchExtension.php) that
allows you to trigger search directly in your templates.

## Search

```twig
{# templates/home.html.twig #}
{% set movies = search('movies', 'green line') %} {# movies will contain a SearchResult object that can be iterated #}

{% for search('movies', 'green line') as movies %} {# A shorter approach could be to use it with for #}
    {{ movies['title'] }}
{% endforeach %}
```

_Note: As The [SearchExtension](../src/Twig/SearchExtension.php) is lazy-loaded, the impact on performances should be minimal,
keep in mind that fetching the MeiliSearch API's done via HTTP, a small latency can occur._

## Scoped search

_Since **0.2**_

Thanks to scoped indexes, you can trigger this type of search directly in Twig:

```twig
{# templates/home.html.twig #}
{% set movies = scoped_search('movies', 'green line') %}

{% for scoped_search('movies', 'green line') as movies %}
    {{ movies['title'] }}
{% endforeach %}
```

More info in [the search documentation](search.md#Scoped indexes).

_Note: As explained in [the search documentation](search.md), if no result can be found, an exception is thrown._
