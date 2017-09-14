const initialState = {
    pending: null,
    items: [],
    chosen: {}
};

export default function fdrs(state = initialState, action) {
    switch (action.type) {
        case 'GET_FDRS_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_FDRS_COMPLETE':
            return { ...state, ...{
                pending: false,
                items: action.payload.response,
                chosen: action.payload.response[0] || {}
            }};
        case 'CHOOSE_FDR':
            return { ...state, ...{
                chosen: action.payload
            }};
        default:
            return state;
    }
}
