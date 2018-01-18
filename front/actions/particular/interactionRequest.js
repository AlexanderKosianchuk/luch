export default function interactionRequest(
  url,
  path,
  payload = {}
) {
  return function(dispatch) {
    let options = {
      credentials: 'same-origin',
      method: 'POST',
      body: payload
    };

    return new Promise((resolve, reject) => {
      try {
        fetch(url + path, options)
        .then(
          (response) => {
            response.json().then((json) => {
              if (response.status === 200) {
                resolve(json);
              } else {
                reject(json);
              }
            });
          },
          (response) => {
            reject(response);
            return response;
          }
        );
      } catch (exception) {
        reject(exception);
      }
    });
  }
};
