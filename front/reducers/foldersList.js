const initialState = {
    pending: null,
    items: []
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

export default function foldersList(state = initialState, action) {
    switch (action.type) {
        case 'GET_FOLDERS':
            return { ...state,
                ...{ pending: true }
            };
        case 'FOLDERS_RECEIVED':
            return {
                pending: false,
                items: action.payload
            };
        case 'FOLDER_DELETED': {
            let deletedIndex = findItemIndex(state.items, action.payload.id);

            if (deletedIndex !== null) {
                state.items.splice(deletedIndex, 1);
            }

            return { ...state, ...{ items: state.items }};
        }
        case 'CREATING_FOLDER_COMPLETE':
            state.items.push(action.payload)
            return { ...state };
        case 'MOVING_FOLDER_COMPLETE': {
            let movedIndex = findItemIndex(state.items, action.payload.id);

            if (movedIndex !== null) {
                state.items[movedIndex].parentId = action.payload.parentId
            }

            return { ...state, ...{ items: state.items }};
        }
        case 'TOGGLING_FOLDER_EXPANDING_COMPLETE':
            let toggledExpandingItem = findItemIndex(state.items, action.payload.id);

            if (toggledExpandingItem !== null) {
                state.items[toggledExpandingItem].expanded
                    = (action.payload.expanded === true);
            }

            return { ...state, ...{ items: state.items }};
        default:
            return state;
    }
}
