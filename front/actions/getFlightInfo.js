import queryString from 'query-string';

export default function getFlightInfoAction(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_INFO_PENDING'
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=flights/getFlightInfo&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'FLIGHT_INFO_RECEIVED',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
