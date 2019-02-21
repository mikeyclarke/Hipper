export interface FormValidationErrors {
    message: string;
    name: string;
    violations: {
        [key: string]: string[]
    };
}