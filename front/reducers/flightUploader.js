const initialState = {
    preview: true,
};

export default function flightUploader(state = initialState, action) {
    switch (action.type) {
        case 'CHANGE_PREVIEW_NEED_STATE':
            state.preview = action.payload;
            return { ...state }
        default:
            return state;
    }
}
