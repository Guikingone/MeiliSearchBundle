# MeiliSearchBundle

MeiliSearchBundle is a Symfony bundle which configure and enable [MeiliSearch]('https://github.com/meilisearch/MeiliSearch').

The core logic act as a wrapper around the official [MeiliSearch PHP SDK]('https://github.com/meilisearch/meilisearch-php').

## Installation

```bash
composer require guikingone/meili-search-bundle
```

```php
// config/bundles.php

```

```yaml
# config/packages/meili_search.yaml
meili_search:
    host: '%env(MEILI_HOST)%'
    api_key: '%env(MEILI_API_KEY)%'
```

## Usage

For a full breakdown of how to use this bundle, please refer to [the documentation](doc).

## Contributing

For a full breakdown of how to contribute to this bundle, please refer to [CONTRIBUTING.md](.github/CONTRIBUTING.md).
