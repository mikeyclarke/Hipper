import HttpClient from 'Http/HttpClient';

export default class FormSubmitService {
    private readonly httpClient: HttpClient;
    private readonly httpStatusOK: number;
    private readonly url: string;

    constructor(httpClient: HttpClient, httpStatusOK: number, url: string) {
        this.httpClient = httpClient;
        this.httpStatusOK = httpStatusOK;
        this.url = url;
    }

    public submit(successCallback: Function, failCallback: Function, payload: object): Promise<void> {
        return this.httpClient.post(
            this.url, {
                json: payload,
            }
        )
        .then((response) => {
            if (this.isResponseOk(response)) {
                successCallback();
            } else {
                response.json().then((res) => {
                    failCallback(res);
                });
            }
        });
    }

    private isResponseOk(response: Response): boolean {
        return response.status === this.httpStatusOK;
    }
}
