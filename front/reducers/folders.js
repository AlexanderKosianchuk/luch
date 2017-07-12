const initialState = {
    pending: null,
    items: [],
    expanded: null
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

export default function folders(state = initialState, action) {
    switch (action.type) {
        case 'GET_FOLDERS_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_FOLDERS_COMPLETE':
            return { ...state, ...{
                    pending: false,
                    items: action.payload.response
                }
            };
        case 'FOLDER_DELETED': {
            let deletedIndex = findItemIndex(state.items, action.payload.id);

            if (deletedIndex !== null) {
                state.items.splice(deletedIndex, 1);
            }

            return { ...state };
        }
        case 'CREATING_FOLDER_COMPLETE':
            state.items.push(action.payload)
            return { ...state };
        case 'MOVING_FOLDER_COMPLETE': {
            let movedIndex = findItemIndex(state.items, action.payload.id);

            if (movedIndex !== null) {
                state.items[movedIndex].parentId = action.payload.parentId
            }

            return { ...state };
        }
        case 'TOGGLING_FOLDER_EXPANDING_COMPLETE':
            let toggledExpandingItem = findItemIndex(state.items, action.payload.id);

            if (toggledExpandingItem !== null) {
                state.items[toggledExpandingItem].expanded
                    = (action.payload.expanded === true);
            }

            return { ...state };
        case 'FOLDER_RENAMED':
            let renamingItem = findItemIndex(state.items, action.payload.id);

            if (renamingItem !== null) {
                state.items[renamingItem].name = action.payload.name;
            }

            return { ...state };
        case 'FOLDER_LIST_EXPANDING_TOGGLE':
            if (typeof action.payload.expanded === 'boolean') {

                state.items.forEach((item, index) => {
                    item.expanded = action.payload.expanded;
                });

                state.expanded = action.payload.expanded;
                return { ...state };
            }

            return state;
        default:
            return state;
    }
}
