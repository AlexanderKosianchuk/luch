import queryString from 'query-string';

export default function applySettlementFilter(payload) {
    return function(dispatch) {
        ;
        dispatch({
            type: 'APPLY_SETTLEMENTS_FILTER',
        });

        let encodeArray = function(array) {
            let str = '';
            array.forEach((item) => {
                str += '&settlements[]=' + item;
            });

            return str;
        }

        let data = encodeArray(payload.chosenSettlements);
        data += '&' + queryString.stringify(payload.flightFilter);

        fetch('/entry.php?action=results/getReport' + data,
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'REPORT_FETCHED',
            payload: json
        }));
    }
};
