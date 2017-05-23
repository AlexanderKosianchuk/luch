const initialState = {
    pending: null,
    errorCode: null
};

export default function authorization(state = initialState, action) {
    switch (action.type) {
        case 'LOGIN_PENDING':
            return {
                pending: true,
                errorCode: null
            };
        case 'USER_LOGGED_IN':
            return {
                pending: false,
                errorCode: null
            };
        case 'USER_LOGGED_OUT':
            return {
                pending: false,
                errorCode: null
            };
        case 'LOGIN_FAILED':
            return {
                pending: false,
                errorCode: action.payload.errorCode || 0
            };
        default:
            return state;
    }
}
