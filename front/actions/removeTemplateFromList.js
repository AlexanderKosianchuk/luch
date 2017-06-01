export default function changeFlightFilterItem(payload) {
    return function(dispatch) {
        dispatch({
            type: 'REMOVE_TEMPLATE_FROM_LIST',
            payload: payload
        });
    }
};
