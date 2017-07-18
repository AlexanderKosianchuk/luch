import queryString from 'query-string';

export default function exportFlight(payload) {
    return function(dispatch) {
        return new Promise((resolve, reject) => {
            let params = '';
            if (Array.isArray(payload)) {
                for (var ii = 0; ii < payload.length; ii++) {
                    params += 'id[]=' + payload[ii] + '&';
                }
            } else {
                params = queryString.stringify(payload);
            }

            fetch('/entry.php?action=flights/itemExport&' + params,
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'FLIGHTS_EXPORTED',
                        payload: payload
                    });
                    window.location = json['zipUrl'];
                    resolve();
                },
                () => reject()
            );
        });
    }
};
