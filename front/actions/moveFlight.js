import queryString from 'query-string';

export default function moveFlight(payload) {
    return function(dispatch) {
        dispatch({
            type: 'MOVING_FLIGHT_START',
            payload: payload
        });

        fetch('/entry.php?action=flights/changeFlightPath&' + queryString.stringify(payload), {
            credentials: "same-origin"
        }).then(response => response.json())
        .then(json => dispatch({
            type: 'MOVING_FLIGHT_COMPLETE',
            payload: json
        }));
    }
};
