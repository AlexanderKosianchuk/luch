import { setLocale } from 'react-redux-i18n';

export default function changeLanguage(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_LANGUAGE_PENDING'
        });

        dispatch(setLocale(payload.language));

        dispatch({
            type: 'LANGUAGE_CHANGED',
            payload: {
                lang: payload.language
            }
        });

        fetch('/entry.php?action=users/userChangeLanguage&lang=' + payload.language,
            { credentials: "same-origin" }
        );
    }
};
