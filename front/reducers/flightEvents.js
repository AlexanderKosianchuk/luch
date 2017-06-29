const initialState = {
    pending: null,
    expandedSections: [],
    items: null,
    isProcessed: false
};

export default function flightEvents(state = initialState, action) {
    switch (action.type) {
        case 'TOGGLE_EVENTS_SECTION':
            let expandedSections = state.expandedSections;
            let index = expandedSections.indexOf(action.payload.section);

            if (action.payload.isShown && (index === -1)) {
                expandedSections.push(action.payload.section)
                return {
                    expandedSections: expandedSections,
                };
            }

            if (!action.payload.isShown && (index > -1)) {
                expandedSections.splice(index, 1);
                return {
                    expandedSections: expandedSections,
                };
            }

            return state;
        case 'FLIGHT_EVENTS_FETCHING':
            return { ...state,
                ...{ pending: true }
            };
        case 'FLIGHT_EVENTS_FETCHED':
            return { ...state,
                ...{
                    pending: false,
                    isProcessed: action.payload.isProcessed,
                    items: action.payload.items
                }
            };
        default:
            return state;
    }
}
