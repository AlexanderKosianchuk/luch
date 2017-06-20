const initialState = {
    pending: null,
    items: null
};

export default function flightsList(state = initialState, action) {
    switch (action.type) {
        case 'GET_FLIGHTS':
            return { ...state,
                ...{ pending: true }
            };
        case 'FLIGHTS_RECEIVED':
            return {
                pending: false,
                items: action.payload
            };
        case 'FLIGHT_DELETED':
            let flightId = action.payload.id;

            if (state.items
                && Array.isArray(state.items)
                && (state.items.length > 0)
            ) {
                state.items.forEach((item, index) => {
                    if (item.id === flightId) {
                        state.items.splice(index, 1);
                    }
                });
            }

            return { ...state, ...{ items: state.items }};
        default:
            return state;
    }
}
