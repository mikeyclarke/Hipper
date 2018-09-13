class SignupService
{
    public static submitForm(callback, payload) {
        return fetch('/_/sign-up', {
            method: "POST",
            headers: {
                "Content-Type": "Content-Type: multipart/form-data;",
            },
            body: payload,
        })
        .then((response) => {
            callback(response.json());
        });
    }
}

export default SignupService;
