import queryString from 'query-string';

export default function createFolder(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CREATING_FOLDER_START',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=folder/createFolder', {
                method: 'post',
                credentials: 'same-origin',
                headers: { 'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: queryString.stringify({
                    data: JSON.stringify(payload)
                })
            }).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'CREATING_FOLDER_COMPLETE',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
