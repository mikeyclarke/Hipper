function submitSignup(callback: Function, payload: string) {
    return fetch('/_/sign-up', {
        headers: {
            'Content-Type': 'application/json',
        },
        method: 'POST',
        body: payload,
    })
    .then((response) => {
        callback(response);
    });
}

export { submitSignup };
