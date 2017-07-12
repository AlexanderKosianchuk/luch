import queryString from 'query-string';

export default function toggleFolderExpanding(payload) {
    return function(dispatch) {
        dispatch({
            type: 'TOGGLING_FOLDER_EXPANDING_START',
            payload: payload
        });

        fetch('/entry.php?action=folder/toggleFolderExpanding&' + queryString.stringify(payload), {
            credentials: "same-origin"
        }).then(response => response.json())
        .then(json => dispatch({
            type: 'TOGGLING_FOLDER_EXPANDING_COMPLETE',
            payload: json
        }));
    }
};
