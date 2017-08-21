const initialState = {
    pending: null,
    items: [],
    chosenItems: []
};

export default function flights(state = initialState, action) {
    switch (action.type) {
        case 'GET_FLIGHTS_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_FLIGHTS_COMPLETE':
            return {
                ...state, ...{
                    pending: false,
                    items: action.payload.response
                }
            };
        case 'DELETE_FLIGHT_COMPLETE': {
            let deletedIndex = state.items.findIndex((item) => {
                return item.id === action.payload.request.id;
            });

            if (deletedIndex !== null) {
                state.items.splice(deletedIndex, 1);
            }

            deletedIndex = state.chosenItems.findIndex((item) => {
                return item.id === action.payload.request.id
            });

            if (deletedIndex !== null) {
                state.chosenItems.splice(deletedIndex, 1);
            }

            return { ...state };
        }
        case 'PUT_FLIGHT_PATH_COMPLETE': {
            let movedIndex = state.items.findIndex((item) => {
                return item.id === action.payload.request.id;
            });

            if (movedIndex !== null) {
                state.items[movedIndex].parentId = action.payload.request.parentId
            }

            return { ...state };
        }
        case 'FLIGHT_UPLOADING_COMPLETE':
            if (typeof action.payload.item === 'object') {
                state.items.push(action.payload.item);
                return { ...state };
            }

            return state;
        case 'FLIGHTS_CHOISE_TOGGLE':
            let chosenIndex = state.items.findIndex((item) => {
                return item.id === action.payload.id;
            });

            let chosenItemsIndex = state.chosenItems.findIndex((item) => {
                return item.id === action.payload.id;
            });

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
        case 'FLIGHTS_UNCHOOSE_ALL':
            state.chosenItems = [];
            return { ...state };
        default:
            return state;
    }
}
