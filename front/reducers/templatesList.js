const initialState = {
    pending: null,
    items: {},
};

export default function templatesList(state = initialState, action) {
    switch (action.type) {
        case 'FLIGHT_TEMPLATES_RECEIVING':
            return {
                ...state,
                ...{ pending: true }
            };
        case 'FLIGHT_TEMPLATES_FETCHED':
            return {
                pending: false,
                items: action.payload
            };
        case 'REMOVING_TEMPLATE_FROM_LIST':
            return {
                ...state,
                ...{ pending: true }
            };
        case 'TEMPLATE_REMOVED_FROM_LIST':
            let newItems = [];
            state.items.forEach((item) => {
                if (item.name !== action.payload.templateName) {
                    newItems.push(item);
                }
            });
            return {
                pending: false,
                items: newItems
            };
        default:
            return state;
    }
}
