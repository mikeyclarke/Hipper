import { PopoverAlert } from 'components/PopoverAlert';
import ky, { Options, ResponsePromise } from 'ky';

type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;

enum RequestMethod {
    GET = 'GET',
    PUT = 'PUT',
    POST = 'POST',
    DELETE = 'DELETE',
    PATCH = 'PATCH',
    HEAD = 'HEAD',
}

const safeHttpMethods = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

export class HttpClient {
    private csrfToken: string;
    private lastRequest: [Request | URL | string, RequestMethod, Options] | null = null;

    constructor(
        csrfToken: string
    ) {
        this.csrfToken = csrfToken;
    }

    public get(url: Request | URL | string, options: Omit<Options, 'body'> = {}): ResponsePromise {
        return this.makeRequest(url, RequestMethod.GET, options);
    }

    public post(url: Request | URL | string, options: Options = {}): ResponsePromise {
        return this.makeRequest(url, RequestMethod.POST, options);
    }

    public put(url: Request | URL | string, options: Options = {}): ResponsePromise {
        return this.makeRequest(url, RequestMethod.PUT, options);
    }

    public patch(url: Request | URL | string, options: Options = {}): ResponsePromise {
        return this.makeRequest(url, RequestMethod.PATCH, options);
    }

    public head(url: Request | URL | string, options: Omit<Options, 'body'> = {}): ResponsePromise {
        return this.makeRequest(url, RequestMethod.HEAD, options);
    }

    public delete(url: Request | URL | string, options: Options = {}): ResponsePromise {
        return this.makeRequest(url, RequestMethod.DELETE, options);
    }

    private makeRequest(
        url: Request | URL | string,
        method: RequestMethod,
        options: Options
    ): ResponsePromise {
        this.lastRequest = [url, method, options];
        const defaultOptions = this.getDefaultOptions(method);
        const requestOptions = Object.assign({}, defaultOptions, options);
        return ky(url, requestOptions);
    }

    private getDefaultOptions(method: RequestMethod): object {
        const options = {
            method: method,
            headers: new Headers({
                'X-Requested-With': 'Fetch',
            }),
            hooks: {
                afterResponse: [
                    this.checkResponse.bind(this),
                ],
            }
        };

        if (this.requestRequiresCsrfToken(method)) {
            options.headers.append('X-CSRF-Token', this.csrfToken);
        }

        return options;
    }

    private requestRequiresCsrfToken(method: RequestMethod): boolean {
        return !safeHttpMethods.includes(method);
    }

    private checkResponse(response: Response): ResponsePromise | void {
        if (response.ok) {
            return;
        }

        if (response.status === 400) {
            return;
        }

        if (response.status === 419 && response.headers.has('X-CSRF-Reset') && null !== this.lastRequest) {
            this.csrfToken = <string> response.headers.get('X-CSRF-Reset');
            return this.makeRequest(...this.lastRequest);
        }

        createAlert();
    }
}

function createAlert(): void {
    const title = 'Something went wrong';
    const message = 'If the issue persists please get in touch with our support team';
    const popoverAlert = <PopoverAlert> document.createElement('popover-alert');
    popoverAlert.setAttribute('alert-title', title);
    popoverAlert.setAttribute('alert-message', message);
    popoverAlert.setAttribute('alert-type', 'error');
    document.body.appendChild(popoverAlert);
}
