
export default function getFlightsList(payload) {
    return function(dispatch) {
        dispatch({
            type: 'GET_FLIGHTS'
        });

        fetch('/entry.php?action=flights/getFlights',
            { credentials: 'same-origin' }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'FLIGHTS_RECEIVED',
            payload: json
        }));
    }
};
