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

_Note: As The [SearchExtension](../src/Twig/SearchExtension.php) is lazy-loaded, the impact on performances should be minimal,
keep in mind that fetching the MeiliSearch API's done via HTTP, a small latency can occur_
