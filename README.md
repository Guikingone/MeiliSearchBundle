# MeiliSearchBundle

MeiliSearchBundle is a Symfony bundle which configure and enable [MeiliSearch]('https://github.com/meilisearch/MeiliSearch').

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
    api_key: '%env(MEILI_API_KEY)%' # Not required
```

## Usage

## Contribution

First, create a fork of this repository and add a new branch using this simple template:

```bash
git checkout -b [context]/[scope]
```

Here's the allowed contexts:

- feat
- fix
- refactor
- build
- tests
- ci

Here's the allowed scopes:

- client
- command
- dic
- collector
- docker
- tests

Develop and open a new PR on this repository, try to be as complete as possible when describing what your PR brings.
Once the PR is ready, please rebase/squash your commits/branch and add the tag `Ready for review`.
