import queryString from 'query-string';

export default function getFdrCyclo(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FDR_CYCLO_PENDING'
        });

        fetch('/entry.php?action=fdr/getCyclo&' + queryString.stringify(payload),
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'FDR_CYCLO_RECEIVED',
            payload: json
        }));
    }
};
