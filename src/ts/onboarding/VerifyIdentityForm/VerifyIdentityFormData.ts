export class VerifyIdentityFormData {
    private readonly verififcationCode: string;

    constructor(verififcationCode: string) {
        this.verififcationCode = verififcationCode;
    }

    public get(): string {
        return JSON.stringify({
            phrase: this.verififcationCode,
        });
    }
}
