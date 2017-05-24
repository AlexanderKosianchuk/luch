export default function changeFlightParamCheckstate(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_FLIGHT_PARAM_CHECKSTATE',
            payload: payload
        });
    }
};
