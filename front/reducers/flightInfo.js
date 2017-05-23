const initialState = {
    pending: null,
    duration: null,
    stepLength: null
};

export default function flightInfo(state = initialState, action) {
    switch (action.type) {
        case 'FLIGHT_INFO_PENDING':
            return {
                pending: true,
                duration: null,
                stepLength: null
            };
        case 'FLIGHT_INFO_RECEIVED':
            return {
                pending: false,
                duration: action.payload.duration,
                stepLength: action.payload.stepLength
            };
        case 'FLIGHT_INFO_RECEIVING_FAILED':
            return {
                pending: false,
                duration: null,
                stepLength: null
            };
        default:
            return state;
    }
}
