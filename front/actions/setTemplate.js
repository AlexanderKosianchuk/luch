import queryString from 'query-string';

export default function setTemplate(payload) {
    return function(dispatch) {
        dispatch({
            type: 'SETTING_TEMPLATE_START',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=templates/setTemplate', {
                method: 'post',
                credentials: "same-origin",
                headers: { "Content-type": "application/x-www-form-urlencoded; charset=UTF-8" },
                body: queryString.stringify({
                    data: JSON.stringify(payload)
                })
            }).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'SETTING_TEMPLATE_COMPLETE',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
