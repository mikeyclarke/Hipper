export class TeamSubdomainFormData {
    private subdomain: string;

    constructor(subdomain: string) {
        this.subdomain = subdomain;
    }

    public get(): string {
        return JSON.stringify({
            subdomain: this.subdomain,
        });
    }
}
