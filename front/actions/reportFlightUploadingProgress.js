export default function reportFlightUploadingProgress(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_UPLOADING_PROGRESS_CHANGE',
            payload: payload
        });
    }
};
