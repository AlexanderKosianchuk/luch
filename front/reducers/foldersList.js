const initialState = {
    pending: null,
    items: []
};

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
        case 'FOLDER_DELETED':
            let folderId = action.payload.id;

            if (state.items
                && Array.isArray(state.items)
                && (state.items.length > 0)
            ) {
                state.items.forEach((item, index) => {
                    if (item.id === folderId) {
                        state.items.splice(index, 1);
                    }
                });
            }

            return { ...state, ...{ items: state.items }};
        case 'CREATING_FOLDER_COMPLETE':
            state.items.push(action.payload)
            return { ...state };
        default:
            return state;
    }
}
