import queryString from 'query-string';

export default function processFlight(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_PROCESSING_START',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=flights/processFlight&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'FLIGHT_PROCESSING_COMPLETE',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
