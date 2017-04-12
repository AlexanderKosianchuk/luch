const initialState = {
    "uploads": [],
};

export default function flightUploadingState(state = initialState, action) {
    switch (action.type) {
        case 'START_FLIGHT_UPLOADING':
            return { ...state, ...action.payload }
        case 'FLIGHT_UPLOADING_PROGRESS_CHANGE':
            return { ...state, ...action.payload }
        case 'COMPLETE_FLIGHT_UPLOADING':
            return { ...state, ...action.payload }
        default:
            return state;
    }
}
