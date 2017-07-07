const initialState = {
    pending: null,
    expandedSections: [],
    items: null,
    isProcessed: false
};

export default function flightEvents(state = initialState, action) {
    switch (action.type) {
        case 'TOGGLE_EVENTS_SECTION':
            return { ...state,
                ...{ expandedSections: action.payload.expandedSections }
            };
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
        case 'TOGGLING_EVENT_RELIABILITY_COMPLETE':
            let flatItems = [];

            Object.keys(state.items).forEach((key) => {
                flatItems = flatItems.concat(state.items[key]);
            });

            flatItems.forEach((event) => {
                if (event.id === action.payload.eventId) {
                    event.reliability = action.payload.reliability
                }
            });

            return { ...state,
                ...{
                    items: state.items
                }
            };
        default:
            return state;
    }
}
