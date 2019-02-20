function nameTeam(callback: Function, payload: string) {
    return fetch('/_/name-team', {
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

export { nameTeam };
