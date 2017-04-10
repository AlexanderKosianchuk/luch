const initialState = {
    "fdr-type": "",
    "bort": "",
    "flight": "",
    "departure-airport": "",
    "arrival-airport": "",
    "from-date": "",
    "to-date": "",
};

export default function flightFilter(state = initialState, action) {
    switch (action.type) {
        case 'CHANGE_FLIGHT_FILTER_ITEM':
            return { ...state, ...action.payload }
        case 'APPLY_FLIGHT_FILTER':
            return state
        default:
            return state;
    }
}
