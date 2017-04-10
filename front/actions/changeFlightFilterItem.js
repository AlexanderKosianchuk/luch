export default function changeFlightFilterItem(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_FLIGHT_FILTER_ITEM',
            payload: payload
        });
    }
};
