function setTeamSubdomain(callback: Function, payload: string) {
    return fetch('/_/choose-team-url', {
        method: 'POST',
        body: payload,
    })
    .then((response) => {
        callback(response);
    });
}

export { setTeamSubdomain };
