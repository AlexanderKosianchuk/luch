const initialState = {
    selectedFdrType: null,
    selectedCalibration: null,
    preview: true,
};

export default function flightUploader(state = initialState, action) {
    switch (action.type) {
        case 'FDR_TYPES_RECEIVED':
            let ap = action.payload;
            if (ap
                && (ap.length > 0)
                && ap[0].id
            ) {
                state.selectedFdrType = ap[0];
                state.selectedCalibration = [];

                if (ap[0].calibrations
                    && (ap[0].calibrations.length > 0)
                    && ap[0].calibrations[0]
                ) {
                    state.selectedCalibration = ap[0].calibrations[0];
                }

                return { ...state }
            }
        case 'CHANGE_FDR_TYPE':
            state.selectedFdrType = action.payload;
            return { ...state }
        case 'CHANGE_CALIBRATION':
            state.selectedCalibration = action.payload;
            return { ...state }
        case 'CHANGE_PREVIEW_NEED_STATE':
            state.preview = action.payload;
            return { ...state }
        default:
            return state;
    }
}
