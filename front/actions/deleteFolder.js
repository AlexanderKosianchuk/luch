import queryString from 'query-string';

export default function deleteFolder(payload) {
    return function(dispatch) {
        dispatch({
            type: 'DELETING_FOLDER',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=folder/deleteFolder&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'FOLDER_DELETED',
                        payload: payload
                    });
                    resolve();
                },
                () => reject()
            );
        });
    }
};
