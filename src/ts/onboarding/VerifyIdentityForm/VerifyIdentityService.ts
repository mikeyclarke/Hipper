function verifyIdentity(callback: Function, payload: string) {
    return fetch('/_/verify-identity', {
        method: 'POST',
        body: payload,
    })
    .then((response) => {
        callback(response);
    });
}

export { verifyIdentity };
