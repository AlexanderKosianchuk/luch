const initialState = {
    pending: null,
    id: null,
    duration: null,
    stepLength: null,
    startFlightTime: null,
    selectedStartFrame: null,
    selectedEndFrame: null,
    data: {}
};

export default function flightInfo(state = initialState, action) {
    switch (action.type) {
        case 'FLIGHT_INFO_PENDING':
            return { ...state,
                ...{
                    pending: true,
                    id: action.payload.flightId
                }
            };
        case 'FLIGHT_INFO_RECEIVED':
            return { ...state,
                ...{
                    pending: false,
                    duration: action.payload.duration,
                    stepLength: action.payload.stepLength,
                    startFlightTime: action.payload.startFlightTime,
                    selectedStartFrame: 0,
                    selectedEndFrame: action.payload.duration,
                    data: action.payload.data,
                }
            };
        case 'FLIGHT_INFO_RECEIVING_FAILED':
            return {
                ...initialState
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
