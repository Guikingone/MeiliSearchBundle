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
     * @var array<string,array>
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
     * @var array<int,array>
     */
    private $facetFilters;

    public static function within(string $index): Search
    {
        $self = new self();

        $self->in($index);

        return $self;
    }

    public function in(string $index): Search
    {
        $this->index = $index;

        return $this;
    }

    public function max(int $limit): Search
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): Search
    {
        $this->offset = $offset;

        return $this;
    }

    public function query(string $query): Search
    {
        if (preg_match('#\s#', $query) && false === strpos('"', $query)) {
            throw new InvalidSearchConfigurationException('A compound query must be enclosed via double-quotes');
        }

        $this->query = $query;

        return $this;
    }

    public function where(string $field, string $operator, $value): Search
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

    public function andWhere(string $field, string $operator, $value): Search
    {
        $this->where(sprintf(' AND %s', $field), $operator, $value);

        return $this;
    }

    public function orWhere(string $field, string $operator, $value): Search
    {
        $this->where(sprintf(' OR %s', $field), $operator, $value);

        return $this;
    }

    public function not(string $field, string $operator, $value): Search
    {
        $this->where(sprintf('NOT %s', $field), $operator, $value);

        return $this;
    }

    public function match(bool $match = false): Search
    {
        $this->match = $match;

        return $this;
    }

    public function shouldRetrieve(array $retrievedAttributes = []): Search
    {
        $this->retrievedAttributes = empty($retrievedAttributes)
            ? $this->retrievedAttributes
            : implode(',', $retrievedAttributes)
        ;

        return $this;
    }

    /**
     * @param string|array<int,string> $attributesToHighlight
     *
     * @return $this
     */
    public function shouldHighlight($attributesToHighlight): Search
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

    public function addFacetFilter(string $key, string $value): Search
    {
        $this->facetFilters[] = [sprintf('%s:%s', $key, $value)];

        return $this;
    }

    public function addOrFacetFilter(string $key, string $value, string $secondKey, string $secondValue): Search
    {
        $this->facetFilters[] = [[sprintf('%s:%s', $key, $value), sprintf('%s:%s', $secondKey, $secondValue)]];

        return $this;
    }

    public function addAndFacetFilter(string $key, string $value, string $secondKey, string $secondValue): Search
    {
        $this->facetFilters[] = [sprintf('%s:%s', $key, $value), sprintf('%s:%s', $secondKey, $secondValue)];

        return $this;
    }

    public function paginate(string $field, string $operator, $value, int $limit): Search
    {
        empty($this->where) ? $this->where($field, $operator, $value) : $this->andWhere($field, $operator, $value);

        $this->max($limit);

        return $this;
    }

    public function getRaw(): array
    {
        return [
            'index' => $this->index,
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
