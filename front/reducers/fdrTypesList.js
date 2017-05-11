const initialState = {
    pending: null,
    items: null
};

export default function fdrTypesList(state = initialState, action) {
    switch (action.type) {
        case 'GET_FDR_TYPES':
            return { ...state,
                ...{ pending: true }
            };
        case 'FDR_TYPES_RECEIVED':
            return {
                pending: false,
                items: action.payload
            };
        default:
            return state;
    }
}
