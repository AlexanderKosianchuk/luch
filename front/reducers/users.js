import findItemIndex from 'helpers/findItemIndex';

let initialState = {
    pending: null,
    items: [],
    chosenItems: []
}

export default function user(state = initialState, action) {
    let id = -1;
    let index = -1;
    let items = [];
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
        case 'POST_CREATE_USER_COMPLETE':
            items = state.items.slice();
            items.push(action.payload.response)
            return {
                ...state, ...{
                    items: items
                }
            };
        case 'POST_DELETE_USER_COMPLETE':
            id = action.payload.request.userId;
            items = state.items.slice();
            index = items.findIndex((element) => {
                return element.id === id;
            });

            if (index !== -1) items.splice(index, 1);

            return {
                ...state, ...{
                    items: items
                }
            };
        case 'POST_EDIT_USER_COMPLETE':
            id = action.payload.response.id;
            items = state.items.slice();
            index = items.findIndex((element) => {
                return element.id === id;
            });

            if (index !== -1) items.splice(index, 1);

            items.push(action.payload.response)

            return {
                ...state, ...{
                    items: items
                }
            };
        default:
            return state;
    }
}
