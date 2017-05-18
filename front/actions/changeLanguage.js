import { setLocale } from 'react-redux-i18n';

export default function changeLanguage(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_LANGUAGE_PENDING'
        });

        dispatch(setLocale(payload.language));

        fetch('/entry.php?action=user/userChangeLanguage&lang=' . payload.language,
            { credentials: "same-origin" }
        ).then((response) => {
                response
                    .json()
                    .then(json => {
                        if (json.status === 'ok') {
                            dispatch({
                                type: 'LANGUAGE_CHANGED',
                                payload: json
                            });
                        } else {
                            dispatch({
                                type: 'LANGUAGE_CHANGE_FAILED',
                                payload: json
                            });
                        }
                    });
            }, (response) => {
                dispatch({
                    type: 'LANGUAGE_CHANGE_FAILED',
                    payload: response
                });
            }
        );
    }
};
