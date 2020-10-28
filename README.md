# MeiliSearchBundle

![MeiliSearchBundle CI](https://github.com/Guikingone/MeiliSearchBundle/workflows/MeiliSearchBundle%20CI/badge.svg?branch=master)

MeiliSearchBundle is an opiniated Symfony bundle which configure and enable [MeiliSearch](https://github.com/meilisearch/MeiliSearch).

The core logic act as a wrapper around the official [MeiliSearch PHP SDK](https://github.com/meilisearch/meilisearch-php).

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
