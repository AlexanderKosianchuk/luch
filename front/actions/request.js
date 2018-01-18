import formurlencoded from 'form-urlencoded';
import queryString from 'query-string';

export default function request(
  action,
  method,
  actionType = null,
  payload = {}
) {
  return function(dispatch) {
    if (actionType !== null) {
      dispatch({
        type: method.toUpperCase() + '_' + actionType + '_START',
        payload: payload
      });
    }

    let url = '';

    if (!Array.isArray(action)) {
      url = action;
    } else {
      url = ENTRY_URL + action.join('/');
    }

    let options = {
      credentials: 'same-origin',
      method: 'get'
    };

    if (method === 'get') {
      url += '/' + queryString.stringify(payload).replace(/[\&\?=]/g, '/');
    } else {
      options.method = 'post';

      if (isFormData(payload)) {
        options.body = payload;
      } else {
        options.headers = { 'Content-Type' : 'application/x-www-form-urlencoded; utf-8' };
        options.body = isFormData(payload) ? payload : formurlencoded(payload);
      }
    }

    return new Promise((resolve, reject) => {
      try {
        fetch(url, options)
        .then(
          (response) => {
            response.json().then((json) => {
              if (response.status === 200) {
                if (actionType !== null) {
                  dispatch({
                    type: method.toUpperCase() + '_' + actionType + '_COMPLETE',
                    payload: {
                      request: payload,
                      response: json
                    }
                  });
                }

                resolve(json);
              } else {
                if (actionType !== null) {
                  dispatch({
                    type: method.toUpperCase() + '_' + actionType + '_FAIL_PARSE',
                    payload: {
                      request: payload,
                      response: json
                    }
                  });
                }

                reject(json);
              }
            });
          },
          (response) => {
            if (actionType !== null) {
              dispatch({
                type: method.toUpperCase() + '_' + actionType + '_FAIL',
                payload: {
                  request: payload,
                  response: response
                }
              });
            }

            reject(response);
            return response;
          }
        );
      } catch (exception) {
        if (actionType !== null) {
          dispatch({
            type: method.toUpperCase() + '_' + actionType + '_FAIL_REQUEST',
            payload: {
              request: payload,
              response: exception
            }
          });
        }

        reject(exception);
      }
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
