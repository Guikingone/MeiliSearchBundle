<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use MeiliSearchBundle\Exception\InvalidSearchConfigurationException;

use function count;
use function explode;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Search
{
    private const FILTERS_OPERATORS = ['=', '!=', '>', '>=', '<', '<='];

    private const NUMERICAL_FILTERS_OPERATORS = ['>', '>=', '<', '<='];

    private const PRIMARY = 'root';

    public const AND = 'AND';

    public const OR = 'OR';

    public const NOT = 'NOT';

    public const AND_NOT = 'AND NOT';

    private ?string $index = null;

    private int $limit = 20;

    private int $offset = 0;

    private ?string $query = null;

    /**
     * @var array<int|string, array>
     */
    private array $filters = [];

    private ?string $computedFilters = null;

    private bool $match = false;

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
    private ?array $facetFilters = [];

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

    public function query(string $query): self
    {
        if (preg_match('#\s#', $query) && !str_contains('"', $query)) {
            throw new InvalidSearchConfigurationException('A compound query must be enclosed via double-quotes');
        }

        $this->query = $query;

        return $this;
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

    public function where(
        string $field,
        string $operator,
        mixed $value,
        bool $isolated = false,
        string $criteria = self::PRIMARY
    ): self {
        if (!in_array($operator, self::FILTERS_OPERATORS)) {
            throw new InvalidSearchConfigurationException('The given operator is not supported');
        }

        if (in_array($operator, self::NUMERICAL_FILTERS_OPERATORS) && !is_numeric($value)) {
            throw new InvalidSearchConfigurationException(
                sprintf(
                    'The value must be numeric when using a numeric related operator, given "%s"',
                    gettype($value)
                )
            );
        }

        if (self::PRIMARY === $criteria && count($this->filters) !== 0) {
            throw new InvalidSearchConfigurationException(
                sprintf('The %s() cannot be used on an existing search', __METHOD__)
            );
        }

        $value = (is_string($value) && preg_match('#\s#', $value)) ? sprintf('"%s"', $value) : $value;

        $this->filters[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'type' => self::PRIMARY,
        ];

        if (self::AND_NOT === $criteria && $isolated) {
            $filter = explode('AND ', $field);

            $this->computedFilters .= ' ' . self::AND . ' ' . '(' . $filter[1] . ' ' . $operator . ' ' . $value . ')';

            return $this;
        }

        $this->computedFilters .= $isolated ? '(' . sprintf('%s %s ', $field, $operator) . $value . ')' : sprintf(
            '%s %s ',
            $field,
            $operator
        ) . $value;

        return $this;
    }

    public function andWhere(string $field, string $operator, mixed $value, bool $isolated = false): self
    {
        if (count($this->filters) === 0) {
            throw new InvalidSearchConfigurationException(
                sprintf('The %s() cannot be used on an empty search', __METHOD__)
            );
        }

        $this->where(sprintf(' %s %s', self::AND, $field), $operator, $value, $isolated, self::AND);

        return $this;
    }

    public function orWhere(string $field, string $operator, mixed $value, bool $isolated = false): self
    {
        if (count($this->filters) === 0) {
            throw new InvalidSearchConfigurationException(
                sprintf('The %s() cannot be used on an empty search', __METHOD__)
            );
        }

        $this->where(sprintf(' %s %s', self::OR, $field), $operator, $value, $isolated, self::OR);

        return $this;
    }

    public function not(string $field, string $operator, mixed $value, bool $isolated = false): self
    {
        $this->where(sprintf('%s %s', self::NOT, $field), $operator, $value, $isolated, self::NOT);

        return $this;
    }

    public function andNot(string $field, string $operator, mixed $value, bool $isolated = false): self
    {
        if (count($this->filters) === 0) {
            throw new InvalidSearchConfigurationException(
                sprintf('The %s() cannot be used on an empty search', __METHOD__)
            );
        }

        $this->where(sprintf(' %s %s %s', self::AND, self::NOT, $field), $operator, $value, $isolated, self::AND_NOT);

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
            : implode(',', $retrievedAttributes);

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

    public function paginate(string $field, string $operator, mixed $value, int $limit): self
    {
        empty($this->filters) ? $this->where($field, $operator, $value) : $this->andWhere($field, $operator, $value);

        $this->max($limit);

        return $this;
    }

    public function getIndex(): ?string
    {
        return $this->index;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getComputedFilters(): ?string
    {
        return $this->computedFilters;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function shouldReturnMatches(): bool
    {
        return $this->match;
    }

    public function getRaw(): array
    {
        return [
            'index' => $this->index,
            'query' => $this->query,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'filters' => $this->computedFilters,
            'rawFilters' => $this->filters,
            'matches' => $this->match,
            'attributesToRetrieve' => $this->retrievedAttributes,
            'attributesToHighlight' => $this->highLightedAttributes,
            'facetFilters' => $this->facetFilters,
        ];
    }
}
