const initialState = {
    pending: null,
    activeTemplate: null // DO NOT POPULATE TMP SOLUTION UNTILL IS NOT REACT COMPONENT
};

export default function templates(state = initialState, action) {
    switch (action.type) {
        case 'GETTING_TEMPLATE_COMPLETE':
            return {
                pending: false,
                activeTemplate: action.payload
            };
        default:
            return state;
    }
}
