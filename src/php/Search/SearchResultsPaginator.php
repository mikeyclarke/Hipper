<?php
declare(strict_types=1);

namespace Hipper\Search;

class SearchResultsPaginator
{
    const RESULTS_PER_PAGE = 10;

    private int $limit;
    private int $offset;

    public function __construct(
        int $numberOfPages,
        int $startFromPage
    ) {
        $this->limit = (self::RESULTS_PER_PAGE * $numberOfPages) + 1;
        $this->offset = self::RESULTS_PER_PAGE * ($startFromPage - 1);
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function hasMoreResults(array $results): bool
    {
        return count($results) === $this->limit;
    }

    public function filterResults(array $results): array
    {
        if ($this->hasMoreResults($results)) {
            array_pop($results);
        }

        return $results;
    }
}
