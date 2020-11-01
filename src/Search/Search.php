<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use MeiliSearchBundle\Exception\InvalidSearchConfigurationException;
use function implode;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function gettype;
use function preg_match;
use function sprintf;
use function strpos;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Search
{
    private const FILTERS_OPERATORS = ['=', '!=', '>', '>=', '<', '<='];
    private const NUMERICAL_FILTERS_OPERATORS = ['>', '>=', '<', '<='];

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $search;

    /**
     * @var int
     */
    private $limit = 20;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var string
     */
    private $query;

    /**
     * @var array<string, array>
     */
    private $where = [];

    /**
     * @var bool
     */
    private $match = false;

    /**
     * @var string
     */
    private $retrievedAttributes = '*';

    /**
     * @var string|null
     */
    private $highLightedAttributes;

    /**
     * @var array<int, array>
     */
    private $facetFilters;

    public static function within(string $index): self
    {
        $self = new self();

        $self->in($index);

        return $self;
    }

    public function in(string $index): self
    {
        $this->index = $index;

        return $this;
    }

    public static function on(string $index, string $query): self
    {
        $self = new self();

        $self->in($index);
        $self->query($query);

        return $self;
    }

    public function max(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function query(string $query): self
    {
        if (preg_match('#\s#', $query) && false === strpos('"', $query)) {
            throw new InvalidSearchConfigurationException('A compound query must be enclosed via double-quotes');
        }

        $this->query = $query;

        return $this;
    }

    public function where(string $field, string $operator, $value): self
    {
        if (!in_array($operator, self::FILTERS_OPERATORS)) {
            throw new InvalidSearchConfigurationException('The given operator is not supported');
        }

        if (in_array($operator, self::NUMERICAL_FILTERS_OPERATORS) && !is_numeric($value)) {
            throw new InvalidSearchConfigurationException(sprintf(
                'The value must be numeric when using a numeric related operator, given %s',
                gettype($value)
            ));
        }

        $this->where[$field] = [
            'operator' => $operator,
            'value' => (is_string($value) && preg_match('#\s#', $value)) ? sprintf('"%s"', $value) : $value,
        ];

        return $this;
    }

    public function andWhere(string $field, string $operator, $value): self
    {
        $this->where(sprintf(' AND %s', $field), $operator, $value);

        return $this;
    }

    public function orWhere(string $field, string $operator, $value): self
    {
        $this->where(sprintf(' OR %s', $field), $operator, $value);

        return $this;
    }

    public function not(string $field, string $operator, $value): self
    {
        $this->where(sprintf('NOT %s', $field), $operator, $value);

        return $this;
    }

    public function match(bool $match = false): self
    {
        $this->match = $match;

        return $this;
    }

    public function shouldRetrieve(array $retrievedAttributes = []): self
    {
        $this->retrievedAttributes = empty($retrievedAttributes)
            ? $this->retrievedAttributes
            : implode(',', $retrievedAttributes)
        ;

        return $this;
    }

    /**
     * @param string|array<int, string> $attributesToHighlight
     *
     * @return $this
     */
    public function shouldHighlight($attributesToHighlight): self
    {
        if (empty($attributesToHighlight)) {
            return $this;
        }

        if ('*' === $attributesToHighlight) {
            $this->highLightedAttributes = $this->retrievedAttributes;
        }

        if (is_array($attributesToHighlight)) {
            $this->highLightedAttributes = implode(',', $attributesToHighlight);
        }

        return $this;
    }

    public function addFacetFilter(string $key, string $value): self
    {
        $this->facetFilters[] = [sprintf('%s:%s', $key, $value)];

        return $this;
    }

    public function addOrFacetFilter(string $key, string $value, string $secondKey, string $secondValue): self
    {
        $this->facetFilters[] = [[sprintf('%s:%s', $key, $value), sprintf('%s:%s', $secondKey, $secondValue)]];

        return $this;
    }

    public function addAndFacetFilter(string $key, string $value, string $secondKey, string $secondValue): self
    {
        $this->facetFilters[] = [sprintf('%s:%s', $key, $value), sprintf('%s:%s', $secondKey, $secondValue)];

        return $this;
    }

    public function paginate(string $field, string $operator, $value, int $limit): self
    {
        empty($this->where) ? $this->where($field, $operator, $value) : $this->andWhere($field, $operator, $value);

        $this->max($limit);

        return $this;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRaw(): array
    {
        return [
            'index' => $this->index,
            'search' => $this->search,
            'query' => $this->query,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'filters' => $this->warmWhereFilters(),
            'matches' => $this->match,
            'attributesToRetrieve' => $this->retrievedAttributes,
            'attributesToHighlight' => $this->highLightedAttributes,
            'facetFilters' => $this->facetFilters,
        ];
    }

    private function warmWhereFilters(): string
    {
        $filters = '';
        foreach ($this->where as $field => $configuration) {
            $filters .= sprintf('%s %s ', $field, $configuration['operator']) . $configuration['value'];
        }

        return $filters;
    }
}
