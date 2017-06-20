const initialState = {
    pending: null,
    items: {}
};

export default function settings(state = initialState, action) {
    switch (action.type) {
        case 'GET_SETTINGS':
            return { ...state, ...{ pending: true }};
        case 'SETTINGS_RECEIVED':
            return {
                pending: false,
                items: action.payload
            };
        case 'CHANGE_SETTINGS_ITEM':
            let items = state.items;
            let key = action.payload.key;
            let value = action.payload.value;

            if (items && items[key] && (items[key] !== value)) {
                items[key] = value;
            }

            return { ...state };
        case 'SET_SETTINGS':
            return state;
        default:
            return state;
    }
}
