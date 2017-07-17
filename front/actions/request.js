import formurlencoded from 'form-urlencoded';
import queryString from 'query-string';

export default function request(
    action,
    actionType,
    method,
    payload = {}
) {
    return function(dispatch) {
        dispatch({
            type: method.toUpperCase() + '_' + actionType + '_START',
            payload: payload
        });

        let url = ENTRY_URL+'?action='+action.join('/');
        let options = {
            credentials: 'same-origin',
            method: 'get'
        };
        if (method === 'get') {
            url += '&' + queryString.stringify(payload)
        } else {
            options.method = 'post';// until backend do not support REST methods

            if (isFormData(payload)) {
                options.body = payload;
            } else {
                options.headers = { "Content-Type" : "application/x-www-form-urlencoded; utf-8" };
                options.body = isFormData(payload) ? payload : formurlencoded(payload);
            }
        }

        return new Promise((resolve, reject) => {
            fetch(url, options)
            .then(
                (response) => response.json(),
                (response) => {
                    dispatch({
                        type: method.toUpperCase() + '_' + actionType + '_FAIL',
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
                        type: method.toUpperCase() + '_' + actionType + '_COMPLETE',
                        payload: {
                            request: payload,
                            response: json
                        }
                    });
                    resolve(json);
                },
                (json) => {
                    dispatch({
                        type: method.toUpperCase() + '_' + actionType + '_FAIL_PARSE',
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

function isFormData (object) {
    let isFormData = true;

    let formDataMethods = [
        'append',
        'delete',
        'get',
        'getAll',
        'has',
        'set'
    ];

    formDataMethods.forEach((item) => {
        isFormData = isFormData && (typeof object[item] === 'function');
    });

    return isFormData;
}
