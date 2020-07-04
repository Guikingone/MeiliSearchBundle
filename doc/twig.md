# Twig

This bundle provides a [SearchExtension](../src/Twig/SearchExtension.php) that
allows you to trigger search directly in your templates.

```twig
{# templates/home.html.twig #}
{% set movies = search('movies', 'green line') %} {# movies will contain a SearchResult object that can be iterated #}

{% for search('movies', 'green line') as movies %} {# A shorter approach could be to use it with for #}
    {{ movies['title'] }}
{% endforeach %}
```

_Note: The `SearchExtension` is lazy-loaded so the impact on performances should be minimal,
keep in mind that fetch the MeiliSearch API is done via HTTP so a small latency can occurs_
