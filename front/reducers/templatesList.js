const initialState = {
    pending: null,
    chosenItems: [],
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
                ...state, ...{
                    pending: false,
                    items: action.payload
            }};
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
                ...state, ...{
                    pending: false,
                    items: newItems
            }};
        case 'TEMPLATE_CHOSEN':
            if (state.chosenItems.indexOf(action.payload.name) === -1) {
                state.chosenItems.push(action.payload.name)
                return {
                    ...state,
                };
            }
            return state;
        case 'TEMPLATE_UNCHOSEN':
            var index = state.chosenItems.indexOf(action.payload.name);
            state.chosenItems.splice(index, 1);
            return {
                ...state
            };
        default:
            return state;
    }
}
