class SignupService
{
    public static submitForm(callback, payload) {
        return fetch('/_/sign-up', {
            method: "POST",
            body: payload,
        })
        .then((response) => {
            callback(response.json());
        });
    }
}

export default SignupService;
