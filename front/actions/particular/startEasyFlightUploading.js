export default function startEasyFlightUploading(payload) {
  return function(dispatch) {
    dispatch({
      type: 'START_FLIGHT_UPLOADING',
      payload: {
        uploadingUid: payload.uploadingUid
      }
    });

    let checkProgress = function () {
      fetch(ENTRY_URL+'uploader/getUploadingStatus/uploadingUid/'+payload.uploadingUid, {
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
        }

        if (json.status !== 'complete') {
          setTimeout(checkProgress, 1000);
        }
      });
    };

    setTimeout(checkProgress, 1000);

    fetch(ENTRY_URL + 'uploader/flightEasyUpload', {
      method: 'POST',
      body: payload.form,
      credentials: "same-origin"
    }).then((response) => {
      return response.json();
    })
    .then(json => {
      dispatch({
        type: 'FLIGHT_UPLOADING_COMPLETE',
        payload: {
          uploadingUid: payload.uploadingUid,
          item: json.item
        }
      });
    });
  }
};
