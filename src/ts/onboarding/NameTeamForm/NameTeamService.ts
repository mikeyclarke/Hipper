function nameTeam(callback: Function, payload: string) {
    return fetch('/_/name-team', {
        method: 'POST',
        body: payload,
    })
    .then((response) => {
        callback(response);
    });
}

export { nameTeam };
