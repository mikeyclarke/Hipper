class SignupService
{
    public static submitForm(callback, payload) {
        return fetch('/_/sign-up', {
            method: "POST",
            headers: {
                "Content-Type": "application/json; charset=utf-8",
            },
            body: JSON.stringify(payload),
        })
        .then((response) => {
            callback(response.json());
        });
    }
}

export default SignupService;
