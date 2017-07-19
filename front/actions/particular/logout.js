import { push } from 'react-router-redux'

export default function logout(payload) {
    return function(dispatch) {
        dispatch({
            type: 'LOGOUT_PENDING'
        });

        fetch('/entry.php?action=users/userLogout&data=some-data',
            { credentials: "same-origin" }
        ).then((response) => {
                response
                    .json()
                    .then(json => {
                        dispatch({
                            type: 'USER_LOGGED_OUT',
                            payload: json
                        });

                        dispatch(push('/login'));
                    });
            }, (response) => {
                dispatch({
                    type: 'LOGOUT_FAILED',
                    payload: response
                });
            }
        );
    }
};
