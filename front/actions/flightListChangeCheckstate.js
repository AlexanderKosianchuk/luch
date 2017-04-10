export default function flightListChangeCheckstate(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_LIST_CHANGE_CHECKSTATE',
            payload: payload
        });
    }
};
