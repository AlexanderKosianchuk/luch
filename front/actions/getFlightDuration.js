import queryString from 'query-string';

export default function getFlightDuration(payload) {
    return function(dispatch) {
        dispatch({
            type: 'DURATION_PENDING'
        });

        fetch('/entry.php?action=results/getSettlements&' + queryString.stringify(payload),
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'DURATION_RECEIVED',
            payload: json
        }));
    }
};
