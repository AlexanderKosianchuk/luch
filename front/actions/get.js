import queryString from 'query-string';

export default function get(
    action,
    type,
    payload = {}
) {
    return function(dispatch) {
        dispatch({
            type: 'GET_' + type + '_START',
            payload: payload
        });

        return new Promise((resolve, reject) => {
            fetch(ENTRY_URL+'?action='+action + '&' + queryString.stringify(payload),
                { credentials: 'same-origin' }
            )
            .then(
                (response) => response.json(),
                (response) => {
                    dispatch({
                        type: 'GET_' + type + '_FAIL',
                        payload: {
                            request: payload,
                            response: response
                        }
                    });
                    reject(response);
                    return response;
                }
            )
            .then(
                (json) => {
                    dispatch({
                        type: 'GET_' + type + '_COMPLETE',
                        payload: {
                            request: payload,
                            response: json
                        }
                    });
                    resolve(json);
                },
                (json) => {
                    dispatch({
                        type: 'GET_' + type + '_FAIL_PARSE',
                        payload: {
                            request: payload,
                            response: json
                        }
                    });
                    reject(json);
                }
            )
        });
    }
};
