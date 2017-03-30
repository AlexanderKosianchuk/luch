export default function applyFlightFilter(payload) {
    return function(dispatch) {
        dispatch({
            type: 'APPLY_FLIGHT_FILTER'
        });

        function stringify (obj) {
            var pairs = [];
            for (var prop in obj) {
                if (!obj.hasOwnProperty(prop)
                    || (obj[prop] === '')
                ) {
                    continue;
                }
                pairs.push(prop + '=' + obj[prop]);
            }
            return pairs.join('&');
        }

        fetch('/entry.php?action=results/getSettlements&' + stringify(payload),
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'SETTLEMENTS_FETCHED',
            payload: json
        }));
    }
};
