export default function getFdrList(payload) {
    return function(dispatch) {
        dispatch({
            type: 'GET_FDR_TYPES'
        });

        fetch('/entry.php?action=fdr/getFdrTypes',
            { credentials: "same-origin" }
        ).then(response => response.json())
        .then(json => dispatch({
            type: 'FDR_TYPES_RECEIVED',
            payload: json
        }));
    }
};
