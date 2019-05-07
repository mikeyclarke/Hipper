import { PopoverAlert } from 'components/PopoverAlert';
import ky from 'ky';

export class HttpClientFactory {
    public create(): typeof ky {
        const client = ky.extend({
            hooks: {
                afterResponse: [
                    checkResponse,
                ]
            }
        });
        return client;
    }
}

function checkResponse(response: Response): void {
    if (response.ok) {
        return;
    }

    if (response.status === 400) {
        return;
    }

    createAlert();
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
