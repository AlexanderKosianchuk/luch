export default function flightListChoiceToggle(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_LIST_CHOISE_TOGGLE',
            payload: payload
        });
    }
};
