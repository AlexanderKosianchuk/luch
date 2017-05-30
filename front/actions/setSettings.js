import queryString from 'query-string';

export default function setSettings(payload) {
    return function(dispatch) {
        dispatch({
            type: 'SETTING_SETTINGS_START',
            payload: payload
        });

        fetch('/entry.php?action=user/setUserOptions', {
            method: 'post',
            credentials: "same-origin",
            headers: { "Content-type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: queryString.stringify({
                data: JSON.stringify(payload)
            })
        }).then(response => response.json())
        .then(json => dispatch({
            type: 'SETTING_SETTINGS_COMPLETE',
            payload: json
        }));
    }
};
