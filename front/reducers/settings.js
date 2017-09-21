const initialState = {
    pending: null,
    items: {}
};

export default function settings(state = initialState, action) {
    switch (action.type) {
        case 'GET_USER_SETTINGS_START':
            return { ...state, ...{ pending: true }};
        case 'GET_USER_SETTINGS_COMPLETE':
            return {
                pending: false,
                items: action.payload.response
            };
        case 'CHANGE_SETTINGS_ITEM':
            let items = state.items;
            let key = action.payload.key;
            let value = action.payload.value;

            if (items && items[key] && (items[key] !== value)) {
                items[key] = value;
            }

            return { ...state };
        default:
            return state;
    }
}
