const initialState = {
    pending: null,
    duration: null
};

export default function flightInfo(state = {}, action) {
    switch (action.type) {
        case 'DURATION_PENDING':
            return {
                pending: true,
                duration: null
            };
        case 'DURATION_RECEIVED':
            return {
                pending: false,
                duration: action.payload.duration
            };
        case 'DURATION_RECEIVING_FAILED':
            return {
                pending: false,
                duration: action.payload.errorCode || 0
            };
        default:
            return state;
    }
}
