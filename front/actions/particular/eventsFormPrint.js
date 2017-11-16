export default function eventsFormPrint(payload) {
    return function(dispatch) {
        let form = document.createElement('form');
        form.target = '_blank';
        form.method = 'post';
        form.action = ENTRY_URL+'flightEvents/printBlank';
        form.style = 'display:none;';

        let flightIdInput = document.createElement('input');
        flightIdInput.name = 'flightId';
        flightIdInput.value = payload.flightId;
        form.appendChild(flightIdInput);

        payload.sections.forEach((item) => {
            let sectionsInput = document.createElement('input');
            sectionsInput.name = 'sections[]';
            sectionsInput.value = item;
            form.appendChild(sectionsInput);
        });

        let grayscaleInput = document.createElement('input');
        grayscaleInput.name = 'colored';
        grayscaleInput.value = !payload.grayscale;
        form.appendChild(grayscaleInput);

        document.body.appendChild(form);
        form.submit();
        form.remove();

        dispatch({
            type: 'EVENTS_FORM_PRINT',
            payload: payload
        });
    }
};
