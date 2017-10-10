const initialState = {
    pending: null,
    items: [],
    chosen: {},
    chosenCalibration: {}
};

export default function fdrs(state = initialState, action) {
    let chosenCalibration = {};

    switch (action.type) {
        case 'GET_FDRS_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_FDRS_COMPLETE':
            if (action.payload.response[0]
                && action.payload.response[0].calibrations
                && (action.payload.response[0].calibrations.length > 0)
            ) {
                chosenCalibration = action.payload.response[0].calibrations[0];
            }

            return { ...state, ...{
                pending: false,
                items: action.payload.response,
                chosen: action.payload.response[0] || {},
                chosenCalibration: chosenCalibration
            }};
        case 'CHOOSE_FDR':
            if (action.payload.calibrations
                && (action.payload.calibrations.length > 0)
            ) {
                chosenCalibration = action.payload.calibrations[0];
            }

            return { ...state, ...{
                chosen: action.payload,
                chosenCalibration: chosenCalibration
            }};
        case 'CHOOSE_CALIBRATION':
            return { ...state, ...{
                chosenCalibration: action.payload
            }};
        default:
            return state;
    }
}
