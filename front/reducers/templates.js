const initialState = {
    pending: null,
    list: {},
    activeTemplate: null
};

export default function templates(state = initialState, action) {
    switch (action.type) {
        case 'FLIGHT_TEMPLATES_RECEIVING':
            return {
                ...state,
                ... { pending: true }
            };
        case 'FLIGHT_TEMPLATES_FETCHED':
            return {
                pending: false,
                list: action.payload
            };
        case 'GETTING_TEMPLATE_COMPLETE':
            return {
                ...state,
                ... { pending: false, activeTemplate: action.payload }
            };
        default:
            return state;
    }
}
