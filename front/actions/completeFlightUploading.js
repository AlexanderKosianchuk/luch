export default function completeFlightUploading(payload) {
    return function(dispatch) {
        dispatch({
            type: 'COMPLETE_FLIGHT_UPLOADING',
            payload: payload
        });
    }
};
