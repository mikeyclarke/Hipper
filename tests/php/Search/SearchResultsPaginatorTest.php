<?php
declare(strict_types=1);

namespace Hipper\Tests\Search;

use Hipper\Search\SearchResultsPaginator;
use PHPUnit\Framework\TestCase;

class SearchResultsPaginatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider numberOfPagesProvider
     */
    public function limitIsResultsPerPageMultipliedByNumberOfPagesPlusOne($numberOfPages)
    {
        $expected = (SearchResultsPaginator::RESULTS_PER_PAGE * $numberOfPages) + 1;

        $startFromPage = 1;
        $paginator = new SearchResultsPaginator($numberOfPages, $startFromPage);

        $this->assertEquals($expected, $paginator->getLimit());
    }

    /**
     * @test
     * @dataProvider startFromPageProvider
     */
    public function offsetIsStartFromPageMinusOneMultipliedByResultsPerPage($startFromPage)
    {
        $expected = ($startFromPage - 1) * SearchResultsPaginator::RESULTS_PER_PAGE;

        $numberOfPages = 1;
        $paginator = new SearchResultsPaginator($numberOfPages, $startFromPage);

        $this->assertEquals($expected, $paginator->getOffset());
    }

    /**
     * @test
     */
    public function hasMoreResultsWhenResultsCountEqualsLimit()
    {
        $numberOfPages = 1;
        $startFromPage = 1;

        $paginator = new SearchResultsPaginator($numberOfPages, $startFromPage);

        $limit = (SearchResultsPaginator::RESULTS_PER_PAGE * $numberOfPages) + 1;
        $results = [];

        for ($i = 0; $i < $limit; $i++) {
            $results[] = ['result'];
        }

        $this->assertTrue($paginator->hasMoreResults($results));
    }

    /**
     * @test
     */
    public function hasMoreResultsIsFalseWhenResultsCountLessThanLimit()
    {
        $numberOfPages = 1;
        $startFromPage = 1;

        $paginator = new SearchResultsPaginator($numberOfPages, $startFromPage);

        $limit = (SearchResultsPaginator::RESULTS_PER_PAGE * $numberOfPages) + 1;
        $results = [];

        $resultsCount = $limit - 1;
        for ($i = 0; $i < $resultsCount; $i++) {
            $results[] = ['result'];
        }

        $this->assertFalse($paginator->hasMoreResults($results));
    }

    /**
     * @test
     */
    public function filterResultsRemovesLastResultWhenResultsCountEqualsLimit()
    {
        $numberOfPages = 1;
        $startFromPage = 1;

        $paginator = new SearchResultsPaginator($numberOfPages, $startFromPage);

        $limit = (SearchResultsPaginator::RESULTS_PER_PAGE * $numberOfPages) + 1;
        $results = [];

        for ($i = 0; $i < $limit; $i++) {
            $results[] = ['result' . $i];
        }

        $expected = [];
        $expectedResultsCount = $limit - 1;
        for ($i = 0; $i < $expectedResultsCount; $i++) {
            $expected[] = ['result' . $i];
        }

        $this->assertEquals($expected, $paginator->filterResults($results));
    }

    /**
     * @test
     */
    public function filterResultsDoesNotRemoveLastResultWhenResultsCountIsLessThanLimit()
    {
        $numberOfPages = 1;
        $startFromPage = 1;

        $paginator = new SearchResultsPaginator($numberOfPages, $startFromPage);

        $limit = (SearchResultsPaginator::RESULTS_PER_PAGE * $numberOfPages) + 1;
        $results = [];

        $resultsCount = $limit - 1;
        for ($i = 0; $i < $resultsCount; $i++) {
            $results[] = ['result' . $i];
        }

        $this->assertEquals($results, $paginator->filterResults($results));
    }

    public function numberOfPagesProvider()
    {
        return [
            [1],
            [5],
        ];
    }

    public function startFromPageProvider()
    {
        return [
            [1],
            [9],
        ];
    }
}
