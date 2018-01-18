const initialState = {
  status: null,
  io: null,
  bindedEvents: []
};

export default function webSockets(state = initialState, action) {
  switch (action.type) {
    case 'WEBSOCKET_CONNECTED':
      return {
        status: true,
        io: action.payload.io
      };
    case 'WEBSOCKET_EVENT_BINDED':
      if (state.bindedEvents.indexOf(action.payload.eventName) === -1) {
        state.bindedEvents.push(action.payload.eventName);

        return { ...state,
          ...{ bindedEvents: state.bindedEvents }
        };
      }

      return state;
    default:
      return state;
  }
}
