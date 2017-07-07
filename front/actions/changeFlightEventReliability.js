import queryString from 'query-string';

export default function changeFlightEventReliability(payload) {
    return function(dispatch) {
        dispatch({
            type: 'TOGGLING_EVENT_RELIABILITY_START',
            payload: payload
        });

        fetch('/entry.php?action=flightEvents/changeReliability&' + queryString.stringify(payload), {
            credentials: "same-origin"
        }).then(response => response.json())
        .then(json => dispatch({
            type: 'TOGGLING_EVENT_RELIABILITY_COMPLETE',
            payload: json
        }));
    }
};
