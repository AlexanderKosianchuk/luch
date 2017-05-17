import queryString from 'query-string';
import { push } from 'react-router-redux'

export default function logout(payload) {
    return function(dispatch) {
        dispatch({
            type: 'LOGOUT_PENDING'
        });

        fetch('/entry.php?action=user/logout&' + queryString.stringify(payload),
            { credentials: "same-origin" }
        ).then((response) => {
                response
                    .json()
                    .then(json => {
                        if (json.status === 'ok') {
                            dispatch({
                                type: 'USER_LOGGED_OUT',
                                payload: json
                            });

                            dispatch(push('/'));
                        } else {
                            dispatch({
                                type: 'LOGOUT_FAILED',
                                payload: json
                            });
                        }
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
