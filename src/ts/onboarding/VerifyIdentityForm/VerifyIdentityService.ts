function verifyIdentity(callback: Function, payload: string) {
    return fetch('/_/verify-identity', {
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

export { verifyIdentity };
