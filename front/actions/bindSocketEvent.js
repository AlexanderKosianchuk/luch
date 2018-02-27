export default function bindSocketEvent(payload) {
  return function(dispatch) {
    if (payload.bindedEvents.indexOf(payload.ioEvent) >= 0) {
      return null;
    }

    payload.io.socket.get(payload.registerUrl, {}, (resp) => {
      dispatch({
        type: 'WEBSOCKET_EVENT_BINDED',
        payload: {
          eventName: payload.reducerEvent
        }
      });
    });

    payload.io.socket.on(payload.ioEvent, (resp) => {
      dispatch({
        type: payload.reducerEvent,
        payload: {
          request: payload,
          response: resp
        }
      });
    });
  }
};
