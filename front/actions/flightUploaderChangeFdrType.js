export default function flightUploaderChangeFdrType(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_FDR_TYPE',
            payload: payload
        });
    }
};
