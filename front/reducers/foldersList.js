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
            let folderId = action.payload.id;
            let index = findItemIndex(state.items, folderId);

            if (index) {
                state.items.splice(index, 1);
            }

            return { ...state, ...{ items: state.items }};
        }
        case 'CREATING_FOLDER_COMPLETE':
            state.items.push(action.payload)
            return { ...state };
        case 'MOVING_FOLDER_COMPLETE': {
            let folderId = action.payload.id;
            let index = findItemIndex(state.items, folderId);

            if (index) {
                state.items[index].parentId = action.payload.parentId
            }

            return { ...state, ...{ items: state.items }};
        }
        default:
            return state;
    }
}
