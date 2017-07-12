export default function exportFlightCoordinates(payload) {
    return function(dispatch) {
        let form = document.createElement('form');
        form.target = '_blank';
        form.action = ENTRY_URL;
        form.style = 'display:none;';

        let actionInput = document.createElement('input');
        actionInput.name = 'action';
        actionInput.value = 'flights/coordinates';
        form.appendChild(actionInput);

        let flightIdInput = document.createElement('input');
        flightIdInput.name = 'id';
        flightIdInput.value = payload.id;
        form.appendChild(flightIdInput);

        document.body.appendChild(form);
        form.submit();
        form.remove();

        dispatch({
            type: 'FLIGHT_COORDINATES_EXPORTED',
            payload: payload
        });
    }
};
