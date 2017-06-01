export default function setCheckedFlightParams(payload) {
    return function(dispatch) {
        dispatch({
            type: 'SET_CHECKED_FLIGHT_PARAMS',
            payload: payload
        });
    }
};
