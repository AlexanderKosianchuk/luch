export default function changeResultSettlementFilterItem() {
    return function(dispatch) {
        dispatch({
            type: 'APPLY_FLIGHT_FILTER'
        });
    }
};
