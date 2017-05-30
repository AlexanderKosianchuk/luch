export default function getSettings (payload) {
    return function(dispatch) {
        dispatch({
            type: 'GET_SETTINGS'
        });

        fetch('/entry.php?action=user/getUserOptions',
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'SETTINGS_RECEIVED',
            payload: json
        }));
    }
};
