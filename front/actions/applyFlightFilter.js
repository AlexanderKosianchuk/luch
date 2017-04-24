import queryString from 'query-string';

export default function applyFlightFilter(payload) {
    return function(dispatch) {
        dispatch({
            type: 'APPLY_FLIGHT_FILTER'
        });

        fetch('/entry.php?action=results/getSettlements&' + queryString.stringify(payload),
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'SETTLEMENTS_FETCHED',
            payload: json
        }));
    }
};
