const initialState = {
  pending: null,
  id: null,
  duration: null,
  stepLength: null,
  startFlightTime: null,
  selectedStartFrame: null,
  selectedEndFrame: null,
  data: {}
};

export default function flight(state = initialState, action) {
  switch (action.type) {
    case 'GET_FLIGHT_START':
      return { ...state,
        ...{
          pending: true,
          id: action.payload.flightId
        }
      };
    case 'GET_FLIGHT_COMPLETE':
      return { ...state,
        ...{
          pending: false,
          duration: action.payload.response.duration,
          stepLength: action.payload.response.stepLength,
          startFlightTime: action.payload.response.startFlightTime,
          selectedStartFrame: 0,
          selectedEndFrame: action.payload.response.duration,
          data: action.payload.response.data,
        }
      };
    case 'GET_FLIGHT_FAIL':
      return {
        ...initialState
      };
    case 'CHANGE_SELECTED_START_FRAME':
      return { ...state,
        ...{ selectedStartFrame: action.payload }
      };
    case 'CHANGE_SELECTED_END_FRAME':
      return { ...state,
        ...{ selectedEndFrame: action.payload }
      };
    default:
      return state;
  }
}
