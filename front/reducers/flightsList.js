const initialState = {
    pending: null,
    items: null
};

function findItemIndex(items, searchIndex) {
    let itemIndex = null;

    if (items
        && Array.isArray(items)
        && (items.length > 0)
    ) {
        items.forEach((item, index) => {
            if (item.id === searchIndex) {
                itemIndex = index;
            }
        });
    }

    return itemIndex;
}

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
        case 'FLIGHT_DELETED': {
            let flightId = action.payload.id;
            let index = findItemIndex(state.items, flightId);

            if (index) {
                state.items.splice(index, 1);
            }

            return { ...state, ...{ items: state.items }};
        }
        case 'MOVING_FLIGHT_COMPLETE': {
            let flightId = action.payload.id;
            let index = findItemIndex(state.items, flightId);

            if (index) {
                state.items[index].parentId = action.payload.parentId
            }

            return { ...state, ...{ items: state.items }};
        }
        default:
            return state;
    }
}
