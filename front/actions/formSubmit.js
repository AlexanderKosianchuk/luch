export default function formSubmit(payload) {
  return function(dispatch) {
    return new Promise((resolve, reject) => {
      try {
        let form = document.createElement('form');
        form.target = payload.target || '_blank';
        form.method = payload.method || 'post';
        form.action = ENTRY_URL + payload.action;
        form.style = 'display:none;';

        payload.controls.forEach((control) => {
          let input = document.createElement('input');
          input.name = control.name;
          input.value = control.value;
          form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();

        if (payload.eventName) {
          dispatch({
            type: payload.eventName,
            payload: payload
          });
        }
      } catch (exception) {
        reject(exception);
      }
    });
  }
};
