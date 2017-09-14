const initialState = {
    pending: null,
    items: [],
    chosen: {}
};

export default function calibration(state = initialState, action) {
    switch (action.type) {
        case 'GET_CALIBRATIONS_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_CALIBRATION_COMPLETE':
            return { ...state, ...{
                pending: false,
                items: action.payload.response,
                chosen: action.payload.response[0] || {}
            }};
        case 'CHOOSE_CALIBRATION':
            return { ...state, ...{
                chosen: action.payload
            }};
        default:
            return state;
    }
}
