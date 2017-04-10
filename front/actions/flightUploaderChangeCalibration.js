export default function flightUploaderChangeCalibration(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_CALIBRATION',
            payload: payload
        });
    }
};
