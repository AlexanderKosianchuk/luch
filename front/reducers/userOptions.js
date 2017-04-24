const initialState = {};

export default function fdrTypesList(state = initialState, action) {
    switch (action.type) {
        case 'USER_OPTIONS_RECEIVED':
            return { ...state, ...action.payload }
        case 'CHANGE_USER_OPTION_ITEM':
            return { ...state, ...action.payload }
        case 'SET_USER_OPTIONS':
            return state
        default:
            return state;
    }
}
