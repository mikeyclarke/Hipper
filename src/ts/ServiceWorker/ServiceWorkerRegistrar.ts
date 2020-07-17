export default class ServiceWorkerRegistrar {
    private scriptUrl: string;
    private scope: string | null;

    constructor(
        scriptUrl: string,
        scope: string | null = null
    ) {
        this.scriptUrl = scriptUrl;
        this.scope = scope;
    }

    public async register(): Promise<void> {
        if (!this.serviceWorkersAvailable()) {
            throw new Error('Service workers are not available');
        }

        return await this.registerServiceWorker();
    }

    private async registerServiceWorker(): Promise<void> {
        const options: Record<string, string> = {};
        if (null !== this.scope) {
            options.scope = this.scope;
        }
        await navigator.serviceWorker.register(this.scriptUrl, options);
        return Promise.resolve();
    }

    private serviceWorkersAvailable(): boolean {
        return 'serviceWorker' in navigator;
    }
}
