import queryString from 'query-string';

export default function getFlightEvents(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_EVENTS_FETCHING'
        });

        fetch('/entry.php?action=flightEvents/getFlightEvents&' + queryString.stringify(payload),
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'FLIGHT_EVENTS_FETCHED',
            payload: json
        }));
    }
};
