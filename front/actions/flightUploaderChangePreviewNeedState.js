export default function flightUploaderChangePreviewNeedState(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_PREVIEW_NEED_STATE',
            payload: payload
        });
    }
};
