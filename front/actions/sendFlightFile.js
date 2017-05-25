import queryString from 'query-string';
import { push } from 'react-router-redux'

export default function sendFlightFile(payload) {
    return function(dispatch) {
        dispatch({
            type: 'SEND_FLIGHT_FILE_PENDING'
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=uploader/storeFlightFile', {
                method: 'post',
                credentials: "same-origin",
                body: payload
            })
            .then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'FLIGHT_FILE_SENT',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
