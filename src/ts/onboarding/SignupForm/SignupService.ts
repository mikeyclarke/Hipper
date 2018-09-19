function submitSignup(callback: any, payload: any) {
    return fetch('/_/sign-up', {
        method: 'POST',
        body: payload,
    })
    .then((response) => {
        callback(response.json());
    });
}

export { submitSignup };
