export class TeamSubdomainFormData {
    private readonly subdomain: string;

    constructor(subdomain: string) {
        this.subdomain = subdomain;
    }

    public get(): string {
        return JSON.stringify({
            subdomain: this.subdomain,
        });
    }
}
