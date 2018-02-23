const initialState = {
  pending: null,
  flightId: null,
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
    case 'GET_FLIGHT_EVENTS_START':
      return { ...initialState,
        ...{
          pending: true,
          flightId: action.payload.flightId
        }
      };
    case 'GET_FLIGHT_EVENTS_COMPLETE':
      return { ...state,
        ...{
          pending: false,
          isProcessed: action.payload.response.isProcessed,
          items: action.payload.response.items
        }
      };
    case 'POST_CHANGE_EVENT_RELIABILITY_COMPLETE':
      let flatItems = [];

      Object.keys(state.items).forEach((key) => {
        flatItems = flatItems.concat(state.items[key]);
      });

      flatItems.forEach((event) => {
        if (event.id === action.payload.request.eventId) {
          event.reliability = action.payload.request.reliability
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
