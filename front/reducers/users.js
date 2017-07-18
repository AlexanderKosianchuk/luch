import findItemIndex from 'helpers/findItemIndex';

let initialState = {
    pending: null,
    items: [],
    chosenItems: []
}

export default function user(state = initialState, action) {
    switch (action.type) {
        case 'GET_USERS_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_USERS_COMPLETE':
            return {
                ...state, ...{
                    pending: false,
                    items: action.payload.response
                }
            };
        case 'USERS_CHOISE_TOGGLE':
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
        default:
            return state;
    }
}
