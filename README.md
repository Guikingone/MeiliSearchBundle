# MeiliSearchBundle

![MeiliSearchBundle CI](https://github.com/Guikingone/MeiliSearchBundle/workflows/MeiliSearchBundle%20CI/badge.svg?branch=master)
[![SymfonyInsight](https://insight.symfony.com/projects/05c41f57-2d98-4fdb-b07b-53f3795a29fb/mini.svg)](https://insight.symfony.com/projects/05c41f57-2d98-4fdb-b07b-53f3795a29fb)
![PHPCS](https://img.shields.io/github/actions/workflow/status/Guikingone/MeiliSearchBundle/phpcs.yml?branch=master&label=PHPCS)
![PHPStan](https://img.shields.io/github/actions/workflow/status/Guikingone/MeiliSearchBundle/phpstan.yml?branch=master&label=PHPStan)
![PHPUnit](https://img.shields.io/github/actions/workflow/status/Guikingone/MeiliSearchBundle/phpunit.yml?branch=master&label=PHPUnit)
![Shell](https://img.shields.io/github/actions/workflow/status/Guikingone/MeiliSearchBundle/shell.yml?branch=master&label=Shell)
![Security](https://img.shields.io/github/actions/workflow/status/Guikingone/MeiliSearchBundle/security.yml?branch=master&label=Security)

MeiliSearchBundle is an opinionated Symfony bundle which configure and enable [MeiliSearch](https://github.com/meilisearch/MeiliSearch).

The core logic act as a wrapper around the official [MeiliSearch PHP SDK](https://github.com/meilisearch/meilisearch-php).

## Main features

- DTO support (thanks to `Symfony/Serializer`) for documents
- Mapping via YAML/XML/PHP/Attribute
- Document definition via attributes or custom providers
- `Symfony/Messenger` integration
- `Symfony/HttpClient` support
- `Symfony/Cache` integration (fallback, search, CRUD)
- `Symfony/ExpressionLanguage` support for building queries
- Twig integration
- Custom form type
- Support for PHP 8.1+
- Support for `Ramsey/uuid`

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

```bash
composer require guikingone/meili-search-bundle
```

Once installed, time to update the `config/bundles.php`:

```php
// config/bundles.php

return [
    // ...
    MeiliSearchBundle\MeiliSearchBundle::class => ['all' => true],
];
```

Once done, just add a `config/packages/meili_search.yaml`:

```yaml
# config/packages/meili_search.yaml
meili_search:
    host: '%env(MEILI_HOST)%' # Default to http://127.0.0.1
    api_key: '%env(MEILI_API_KEY)%' # Optional but recommended in development mode
```

## Usage

For a full breakdown of how to use this bundle, please refer to [the documentation](doc).

## Contributing

For a full breakdown of how to contribute to this bundle, please refer to [CONTRIBUTING.md](.github/CONTRIBUTING.md).
