export default function getUserOptions (payload) {
    return function(dispatch) {
        dispatch({
            type: 'GET_USER_OPTIONS'
        });

        fetch('/entry.php?action=user/getUserOptions',
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'USER_OPTIONS_RECEIVED',
            payload: json
        }));
    }
};
