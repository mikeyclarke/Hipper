function submitSignup(callback: Function, payload: FormData) {
    return fetch('/_/sign-up', {
        method: 'POST',
        body: payload,
    })
    .then((response) => {
        callback(response.json());
    });
}

export { submitSignup };
