const initialState = {
    pending: null,
    items: [],
    chosenItems: []
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
                ...state, ...{
                    pending: false,
                    items: action.payload
                }
            };
        case 'FLIGHT_DELETED': {
            let deletedIndex = findItemIndex(state.items, action.payload.id);

            if (deletedIndex !== null) {
                state.items.splice(deletedIndex, 1);
            }

            deletedIndex = findItemIndex(state.chosenItems, action.payload.id);

            if (deletedIndex !== null) {
                state.chosenItems.splice(deletedIndex, 1);
            }

            return { ...state };
        }
        case 'MOVING_FLIGHT_COMPLETE': {
            let movedIndex = findItemIndex(state.items, action.payload.id);

            if (movedIndex !== null) {
                state.items[movedIndex].parentId = action.payload.parentId
            }

            return { ...state };
        }
        case 'FLIGHT_UPLOADING_COMPLETE':
            if (typeof action.payload.item === 'object') {
                state.items.push(action.payload.item);
                return { ...state };
            }

            return state;
        case 'FLIGHT_LIST_CHOISE_TOGGLE':
            let chosenIndex = findItemIndex(state.items, action.payload.id);
            let chosenItemsIndex = findItemIndex(state.chosenItems, action.payload.id);

            if ((typeof chosenItemsIndex === 'number')
                 && (action.payload.checkstate === true)
            ) {
                return state;
            }

            if ((typeof chosenItemsIndex !== 'number')
                 && (action.payload.checkstate === false)
            ) {
                return state;
            }

            if ((typeof chosenItemsIndex !== 'number')
                 && (action.payload.checkstate === true)
            ) {
                state.chosenItems.push(state.items[chosenIndex]);
                return { ...state };
            }

            if ((typeof chosenItemsIndex === 'number')
                 && (action.payload.checkstate === false)
            ) {
                state.chosenItems.splice(chosenItemsIndex, 1);
                return { ...state };
            }

            return state;
            case 'FLIGHT_LIST_UNCHOOSE_ALL':
                state.chosenItems = [];
                return { ...state };
        default:
            return state;
    }
}
