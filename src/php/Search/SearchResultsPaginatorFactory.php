<?php
declare(strict_types=1);

namespace Hipper\Search;

class SearchResultsPaginatorFactory
{
    public function create(int $numberOfPages, int $startFromPage): SearchResultsPaginator
    {
        return new SearchResultsPaginator($numberOfPages, $startFromPage);
    }
}
