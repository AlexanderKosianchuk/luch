const initialState = {
    pending: null,
    name: {},
    servisePurpose: {},
    ap: {},
    bp: {}
};

export default function templateInfo(state = initialState, action) {
    switch (action.type) {
        case 'GETTING_TEMPLATE_START':
            return {
                ...state,
                ...{ pending: true }
            };
        case 'GETTING_TEMPLATE_COMPLETE':
            return {
                pending: false,
                name: action.payload.name,
                servisePurpose: action.payload.servisePurpose, 
                ap: action.payload.ap,
                bp: action.payload.bp
            };
        default:
            return state;
    }
}
