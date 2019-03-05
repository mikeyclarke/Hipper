export class FormSubmitService {
    private readonly httpStatusOK: number;
    private readonly url: string;

    constructor(httpStatusOK: number, url: string) {
        this.httpStatusOK = httpStatusOK;
        this.url = url;
    }

    public submit(successCallback: Function, failCallback: Function, payload: string): Promise<void> {
        return fetch(this.url, {
            headers: {
                'Content-Type': 'application/json',
            },
            method: 'POST',
            body: payload,
        })
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
