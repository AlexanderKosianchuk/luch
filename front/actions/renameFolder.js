import queryString from 'query-string';

export default function renameFolder(payload) {
    return function(dispatch) {
        dispatch({
            type: 'RENAMING_FOLDER',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=folder/renameFolder&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'FOLDER_RENAMED',
                        payload: payload
                    });
                    resolve();
                },
                () => reject()
            );
        });
    }
};
