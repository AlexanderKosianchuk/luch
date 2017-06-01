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
                ...{ pending: true }
            };
        case 'FLIGHT_TEMPLATES_FETCHED':
            return {
                pending: false,
                list: action.payload
            };
        case 'GETTING_TEMPLATE_COMPLETE':
            return {
                ...state,
                ...{ pending: false, activeTemplate: action.payload }
            };
        case 'REMOVE_TEMPLATE_FROM_LIST':
            return state;
        default:
            return state;
    }
}
