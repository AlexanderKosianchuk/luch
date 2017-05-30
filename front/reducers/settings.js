const initialState = {};

export default function fdrTypesList(state = initialState, action) {
    switch (action.type) {
        case 'SETTINGS_RECEIVED':
            return { ...state, ...action.payload }
        case 'CHANGE_SETTINGS_ITEM':
            return { ...state, ...action.payload }
        case 'SET_SETTINGS':
            return state
        default:
            return state;
    }
}
