import queryString from 'query-string';

export default function mergeTemplates(payload) {
    return function(dispatch) {
        dispatch({
            type: 'MERGING_TEMPLATES_START',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch(ENTRY_URL + '?action=templates/mergeTemplates', {
                method: 'post',
                credentials: "same-origin",
                headers: { "Content-type": "application/x-www-form-urlencoded; charset=UTF-8" },
                body: queryString.stringify({
                    data: JSON.stringify(payload)
                })
            }).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'MERGING_TEMPLATES_COMPLETE',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
