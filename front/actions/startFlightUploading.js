export default function startFlightUploading(payload) {
    return function(dispatch) {
        dispatch({
            type: 'START_FLIGHT_UPLOADING',
            payload: payload
        });
    }
};
