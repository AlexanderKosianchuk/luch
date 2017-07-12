import queryString from 'query-string';
import { push } from 'react-router-redux'
import { setLocale } from 'react-redux-i18n';

export default function login(payload) {
    return function(dispatch) {
        dispatch({
            type: 'LOGIN_PENDING'
        });

        fetch('/entry.php?action=users/login&' + queryString.stringify(payload),
            { credentials: "same-origin" }
        ).then((response) => {
                response
                    .json()
                    .then(json => {
                        if (json.status === 'ok') {
                            dispatch({
                                type: 'USER_LOGGED_IN',
                                payload: json
                            });

                            if (json.lang && json.lang.length === 2) {
                                dispatch(setLocale(json.lang.toLowerCase()));
                            }

                            dispatch(push('/'));
                        } else {
                            dispatch({
                                type: 'LOGIN_FAILED',
                                payload: json
                            });
                        }
                    });
            }, (response) => {
                dispatch({
                    type: 'LOGIN_FAILED',
                    payload: response
                });
            }
        );
    }
};
