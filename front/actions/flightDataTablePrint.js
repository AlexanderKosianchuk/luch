import queryString from 'query-string';

export default function flightDataTablePrint(payload) {
    return function(dispatch) {
        let form = document.createElement('form');
        form.target = '_blank';
        form.action = ENTRY_URL;
        form.style = 'display:none;';

        let actionInput = document.createElement('input');
        actionInput.name = 'action';
        actionInput.value = 'chart/figurePrint';
        form.appendChild(actionInput);

        let flightIdInput = document.createElement('input');
        flightIdInput.name = 'flightId';
        flightIdInput.value = payload.flightId;
        form.appendChild(flightIdInput);

        let startFrameInput = document.createElement('input');
        startFrameInput.name = 'startFrame';
        startFrameInput.value = payload.startFrame;
        form.appendChild(startFrameInput);

        let endFrameInput = document.createElement('input');
        endFrameInput.name = 'endFrame';
        endFrameInput.value = payload.endFrame;
        form.appendChild(endFrameInput);

        payload.analogParams.forEach((item) => {
            let analogParamsInput = document.createElement('input');
            analogParamsInput.name = 'analogParams[]';
            analogParamsInput.value = item.code || '';
            form.appendChild(analogParamsInput);
        });

        payload.binaryParams.forEach((item) => {
            let binaryParamsInput = document.createElement('input');
            binaryParamsInput.name = 'binaryParams[]';
            binaryParamsInput.value = item.code || '';
            form.appendChild(binaryParamsInput);
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();

        dispatch({
            type: 'CHART_TABLE_PRINT',
            payload: payload
        });
    }
};
