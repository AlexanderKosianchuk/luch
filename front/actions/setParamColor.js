import queryString from 'query-string';

export default function setParamColor(payload) {
    return function(dispatch) {
        dispatch({
            type: 'SETTING_PARAM_COLOR_START',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch('/entry.php?action=fdr/setParamColor&' + queryString.stringify(payload),
                { credentials: "same-origin" }
            ).then(response => response.json())
            .then(json => {
                    dispatch({
                        type: 'SETTING_PARAM_COLOR_COMPLETE',
                        payload: json
                    });
                    resolve();
                },
                () => reject()
            )
        });
    }
};
