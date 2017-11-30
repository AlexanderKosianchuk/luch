export default function userReducer(state = {}, action) {
    switch (action.type) {
        case 'USER_LOGGED_IN': {
            let pl = action.payload;
            if (pl && pl.login && (pl.login.length > 3) && pl.lang) {
                return {
                    login: pl.login,
                    role: pl.role,
                    lang: pl.lang
                };
            } else {
                return {};
            }
        }
        case 'PUT_LANGUAGE_COMPLETE': {
            let pl = action.payload;
            if (pl.lang && state.lang !== pl.lang) {
                return {
                    ...state,
                    ... {
                        lang: pl.lang
                    }
                };
            }
        }
        case 'USER_LOGGED_OUT':
            return {};
        default:
            return state;
    }
}
