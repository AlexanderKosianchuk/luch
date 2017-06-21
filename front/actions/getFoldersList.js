
export default function getFoldersList(payload) {
    return function(dispatch) {
        dispatch({
            type: 'GET_FOLDERS'
        });

        fetch('/entry.php?action=folder/getFolders',
            { credentials: 'same-origin' }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'FOLDERS_RECEIVED',
            payload: json
        }));
    }
};
