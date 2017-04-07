const initialState = [];

export default function fdrTypesList(state = initialState, action) {
    switch (action.type) {
        case 'FDR_TYPES_RECEIVED':
            return { ...state, ...action.payload }
        default:
            return state;
    }
}
