export class SignupFormData {
    private readonly name: string;
    private readonly email: string;
    private readonly password: string;
    private readonly termsAgreed: boolean;

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
