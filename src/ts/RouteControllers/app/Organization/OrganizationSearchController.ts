import SearchResultsPaginator from 'Search/SearchResultsPaginator';

export default class OrganizationSearchController {
    private readonly searchResultsPaginator: SearchResultsPaginator;

    constructor(
        searchResultsPaginator: SearchResultsPaginator
    ) {
        this.searchResultsPaginator = searchResultsPaginator;
    }

    public start(): void {
        const searchResultsList = document.querySelector('.js-search-results-list');
        if (!(searchResultsList instanceof HTMLOListElement)) {
            return;
        }

        this.searchResultsPaginator.enablePagination(searchResultsList);
    }
}
