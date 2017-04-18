export default function startEasyFlightUploading(payload) {
    return function(dispatch) {
        dispatch({
            type: 'START_FLIGHT_UPLOADING',
            payload: {
                uploadingUid: payload.uploadingUid
            }
        });

        let checkProgress = function () {
            fetch('/entry.php?action=uploader/getUploadingStatus&uploadingUid='+payload.uploadingUid, {
                method: 'GET',
                credentials: "same-origin"
            })
            .then((response) => {
                try {  return response.json() }
                catch(e) {
                    setTimeout(checkProgress, 1000);
                }
            })
            .then((json) => {
                if (!json) {
                    return;
                }

                if (json.status === 'ok') {
                    dispatch({
                        type: 'FLIGHT_UPLOADING_PROGRESS_CHANGE',
                        payload: {
                            uploadingUid: json.uploadingUid,
                            progress: json.progress
                        }
                    });
                    setTimeout(checkProgress, 1000);
                } else if (json.status === 'complete') {
                    dispatch({
                        type: 'FLIGHT_UPLOADING_COMPLETE',
                        payload: {
                            uploadingUid: payload.uploadingUid
                        }
                    });
                } else {
                    setTimeout(checkProgress, 1000);
                }
            });
        };

        setTimeout(checkProgress, 1000);

        fetch('/entry.php?action=uploader/flightEasyUpload', {
            method: 'POST',
            body: payload.form,
            credentials: "same-origin"
        }).then((response) => {
            response.json();
        })
        .then(json => {
            dispatch({
                type: 'FLIGHT_UPLOADING_COMPLETE',
                payload: {
                    uploadingUid: payload.uploadingUid
                }
            });
        });
    }
};
