export default function flightListUnchooseAll() {
    return function(dispatch) {
        dispatch({
            type: 'FLIGHT_LIST_UNCHOOSE_ALL'
        });
    }
};
