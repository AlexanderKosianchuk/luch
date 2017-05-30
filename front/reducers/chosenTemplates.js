const initialState = {
    list: [],
};

export default function chosenTemplates(state = initialState, action) {
    switch (action.type) {
        case 'TEMPLATE_CHOSEN':
            if (state.list.indexOf(action.payload.name) === -1) {
                state.list.push(action.payload.name)
                return {
                    ...state,
                };
            }
            return state;
        case 'TEMPLATE_UNCHOSEN':
            var index = state.list.indexOf(action.payload.name);
            state.list.splice(index, 1);
            return {
                ...state
            };
        default:
            return state;
    }
}
