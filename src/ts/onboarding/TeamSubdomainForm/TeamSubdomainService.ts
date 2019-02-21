function setTeamSubdomain(successCallback: Function, failCallback: Function, payload: string) {
    return fetch('/_/choose-team-url', {
        headers: {
            'Content-Type': 'application/json',
        },
        method: 'POST',
        body: payload,
    })
    .then((response) => {
        if (isResponseOk(response)) {
            successCallback();
        } else {
            response.json().then((res) => {
                failCallback(res);
            });
        }
    });
}

function isResponseOk(response: Response): boolean {
    return response.status === 200;
}

export { setTeamSubdomain };
