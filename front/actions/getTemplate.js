import queryString from 'query-string';

export default function getTemplate(payload) {
    return function(dispatch) {
        dispatch({
            type: 'GETTING_TEMPLATE_START',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=templates/getTemplate&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'GETTING_TEMPLATE_COMPLETE',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
