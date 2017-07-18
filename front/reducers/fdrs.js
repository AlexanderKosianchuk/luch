const initialState = {
    pending: null,
    items: null,
    chosen: null,
    chosenCalibration: null //TODO: remove to separate reducer
};

export default function fdrs(state = initialState, action) {
    switch (action.type) {
        case 'GET_FDRS_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_FDRS_COMPLETE':
            let chosen = {};
            let chosenCalibration = {};

            if (action.payload.response.length > 0) {
                chosen = action.payload.response[0];

                if (chosen.calibrations
                    && (chosen.calibrations.length > 0)
                ) {
                    chosenCalibration = chosen.calibrations[0];
                }
            }

            return {
                pending: false,
                items: action.payload.response,
                chosen: chosen,
                chosenCalibration: chosenCalibration
            };
        case 'CHOOSE_FDR':
            return { ...state, ...{
                chosen: action.payload
            }};
        case 'CHOOSE_CALIBRATION':
            return { ...state, ...{
                chosenCalibration: action.payload
            }};
        default:
            return state;
    }
}
