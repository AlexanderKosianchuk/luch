import queryString from 'query-string';

export default function deleteFlight(payload) {
    return function(dispatch) {
        dispatch({
            type: 'DELETING_FLIGHT',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=flights/deleteFlight&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'FLIGHT_DELETED',
                        payload: payload
                    });
                    resolve();
                },
                () => reject()
            );
        });
    }
};
