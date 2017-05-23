const initialState = {
    pending: null,
    duration: null,
    stepLength: null,
    selectedStartFrame: null,
    selectedEndFrame: null
};

export default function flightInfo(state = initialState, action) {
    switch (action.type) {
        case 'FLIGHT_INFO_PENDING':
            return {
                pending: true,
                duration: null,
                stepLength: null,
                selectedStartFrame: null,
                selectedEndFrame: null
            };
        case 'FLIGHT_INFO_RECEIVED':
            return {
                pending: false,
                duration: action.payload.duration,
                stepLength: action.payload.stepLength,
                selectedStartFrame: 0,
                selectedEndFrame: action.payload.duration
            };
        case 'FLIGHT_INFO_RECEIVING_FAILED':
            return {
                pending: false,
                duration: null,
                stepLength: null,
                selectedStartFrame: null,
                selectedEndFrame: null
            };
        case 'CHANGE_SELECTED_START_FRAME':
            return { ...state,
                ...{ selectedStartFrame: action.payload }
            };
        case 'CHANGE_SELECTED_END_FRAME':
            return { ...state,
                ...{ selectedEndFrame: action.payload }
            };
        default:
            return state;
    }
}
