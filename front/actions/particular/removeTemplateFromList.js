import queryString from 'query-string';

export default function removeTemplateFromList(payload) {
    return function(dispatch) {
        dispatch({
            type: 'REMOVING_TEMPLATE_FROM_LIST',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=templates/removeTemplate&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'TEMPLATE_REMOVED_FROM_LIST',
                        payload: payload
                    });
                    resolve();
                },
                () => reject()
            );
        });
    }
};
