const initialState = {
    "selectedFdrType": null,
    "selectedCalibration": null,
    "preview": true,
};

export default function flightUploader(state = initialState, action) {
    switch (action.type) {
        case 'CHANGE_FDR_TYPE':
            state.selectedFdrType = action.payload;
            return { ...state }
        case 'CHANGE_CALIBRATION':
            state.selectedCalibration = action.payload;
            return { ...state }
        case 'CHANGE_PREVIEW_NEED_STATE':
            state.preview = action.payload;
            return { ...state }
        case 'START_EASY_FLIGHT_UPLOADING':
            return { ...state }
        default:
            return state;
    }
}
