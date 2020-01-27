import HttpClient from 'Http/HttpClient';

const buttonClassName = 'js-more-results-button';

export default class SearchResultsPaginator {
    private readonly httpClient: HttpClient;
    private searchResultsList: HTMLOListElement | null = null;
    private nextPage: number = 2;
    private pathname: string | null = null;
    private urlSearchParams: Record<string, string> = {};

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
    }

    public enablePagination(searchResultsList: HTMLOListElement): void {
        this.searchResultsList = searchResultsList;
        this.cachePathname();
        this.cacheUrlSearchParams();
        this.attachEvents();
    }

    private cachePathname(): void {
        this.pathname = window.location.pathname;
    }

    private cacheUrlSearchParams(): void {
        const searchParams: Record<string, string> = {};
        (new URLSearchParams(window.location.search)).forEach((value, key) => {
            searchParams[key] = value;
        });

        this.urlSearchParams = searchParams;
    }

    private attachEvents(): void {
        if (null === this.searchResultsList) {
            return;
        }

        this.searchResultsList.addEventListener('click', this.onSearchResultsClick.bind(this));
    }

    private onSearchResultsClick(event: MouseEvent): void {
        if (!(event.target instanceof HTMLElement) || !event.target.classList.contains(buttonClassName)) {
            return;
        }

        const button = <HTMLButtonElement> event.target;
        button.disabled = true;

        if (!button.dataset.query) {
            throw new Error('button missing query data attribute');
        }

        if (!button.dataset.endpoint) {
            throw new Error('button missing endpoint data attribute');
        }

        this.fetchMoreResultsHtml(button.dataset.query, button.dataset.endpoint)
            .then((html: string) => {
                this.insertNewResults(html, button);
                this.replaceHistoryState();
                this.incrementPage();
            })
            .catch(() => {
                button.disabled = false;
            });
    }

    private async fetchMoreResultsHtml(searchQuery: string, endpoint: string): Promise<string> {
        const response = await this.httpClient.get(endpoint, {
            searchParams: {
                q: searchQuery,
                page: this.nextPage,
            },
        });
        const json = await response.json();
        return json.html;
    }

    private insertNewResults(html: string, button: HTMLButtonElement): void {
        const fragment = document.createElement('template');
        fragment.innerHTML = html;

        if (null === button.parentElement) {
            throw new Error('button is orphaned');
        }
        button.parentElement.replaceWith(fragment.content);
    }

    private replaceHistoryState(): void {
        if (null === this.urlSearchParams) {
            return;
        }

        const searchParams = this.urlSearchParams;
        searchParams.p = '' + this.nextPage;
        const url = this.pathname + '?' + (new URLSearchParams(searchParams).toString());
        window.history.replaceState(null, document.title, url);
    }

    private incrementPage(): void {
        this.nextPage += 1;
    }
}
