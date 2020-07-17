import ServiceWorkerRegistrar from 'ServiceWorker/ServiceWorkerRegistrar';

const CACHE_SELECTOR = '[data-cache]';

export default class InlineAssetCacher {
    private serviceWorkerRegistrar: ServiceWorkerRegistrar;
    private assetBaseUrl: string;

    constructor(
        serviceWorkerRegistrar: ServiceWorkerRegistrar,
        assetBaseUrl: string
    ) {
        this.serviceWorkerRegistrar = serviceWorkerRegistrar;
        this.assetBaseUrl = assetBaseUrl;
    }

    public async cacheInlineAssets(): Promise<void> {
        try {
            await this.serviceWorkerRegistrar.register();
        } catch (e) {
            return;
        }

        const assets = this.getAssetsToCache();
        if (assets.length === 0) {
            return;
        }

        const cachedAssets = await this.fetchAssets(assets);
        await this.serviceWorkerActivated();
        this.sendAssetDigestToServiceWorker(cachedAssets);
    }

    private async serviceWorkerActivated(): Promise<void> {
        if (null !== navigator.serviceWorker.controller) {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                resolve();
            });
        });
    }

    private sendAssetDigestToServiceWorker(assets: Record<string, string>[]): void {
        navigator.serviceWorker.controller.postMessage({
            name: 'asset_digest_update',
            digest: assets,
        });
    }

    private async fetchAssets(assets: Record<string, string>[]): Promise<Record<string, string>[]> {
        const cachedAssets = [];

        for (const asset of assets) {
            const versionedBundle = asset.bundle.split('.').splice(-1, 0, asset.hash).join('');
            const url = `${this.assetBaseUrl}/build/${versionedBundle}`;
            const response = await fetch(versionedBundle, {
                cache: 'default',
                headers: {
                    'Accept': asset.type,
                },
            });
            if (response.ok) {
                cachedAssets.push({
                    bundle: asset.bundle,
                    hash: asset.hash,
                });
            }
        }

        return cachedAssets;
    }

    private getAssetsToCache(): Record<string, string>[] {
        const elements = document.querySelectorAll(CACHE_SELECTOR);
        const assets = Array.from(elements).map((element: HTMLElement) => {
            return {
                bundle: <string> element.dataset.bundle,
                hash: <string> element.dataset.hash,
                type: this.getMimeTypeByElement(element)
            };
        });
        return assets;
    }

    private getMimeTypeByElement(element: HTMLElement): string {
        if (element instanceof HTMLScriptElement) {
            return 'application/javascript';
        }

        if (element instanceof HTMLStyleElement) {
            return 'text/css';
        }

        throw new Error('Element not supported for caching');
    }
}
