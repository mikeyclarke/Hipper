export class VerifyIdentityFormData {
    private verififcationCode: string;

    constructor(verififcationCode: string) {
        this.verififcationCode = verififcationCode;
    }

    public get(): string {
        return JSON.stringify({
            phrase: this.verififcationCode,
        });
    }
}
