export class SignupFormData {
    private name: string;
    private email: string;
    private password: string;
    private termsAgreed: boolean;

    constructor(name: string, email: string, password: string, termsAgreed: boolean) {
        this.name = name;
        this.email = email;
        this.password = password;
        this.termsAgreed = termsAgreed;
    }

    public get(): string {
        return JSON.stringify({
            name: this.name,
            email_address: this.email,
            password: this.password,
            terms_agreed: this.termsAgreed,
        });
    }
}
