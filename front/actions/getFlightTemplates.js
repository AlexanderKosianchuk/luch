import queryString from 'query-string';

export default function getFlightTemplates(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_TEMPLATES_RECEIVING'
        });

        fetch('/entry.php?action=results/getSettlements&' + queryString.stringify(payload),
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'FLIGHT_TEMPLATES_FETCHED',
            payload: json
        }));
    }
};
