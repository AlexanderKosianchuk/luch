export default function startEasyFlightUploading(payload) {
    return function(dispatch) {
        dispatch({
            type: 'START_EASY_FLIGHT_UPLOADING'
        });

        fetch('/entry.php?action=uploader/flightEasyUpload', {
            method: 'POST',
            body: payload.form,
            credentials: "same-origin"
        }).then(response => response.json())
        .then(json => dispatch({
            type: 'FLIGHT_UPLOADING_COMPLETE',
            payload: payload.uploadingUid
        }));
    }
};
