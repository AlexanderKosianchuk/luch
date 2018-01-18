export default function bindSocketEvent(payload) {
  return function(dispatch) {
    payload.io.socket.get(payload.registerUrl)
      .then(() => {
        dispatch({
          type: 'WEBSOCKET_EVENT_BINDED',
          payload: {
            eventName: reducerEvent
          }
        });
      });

    payload.io.socket.on(payload.ioEvent, (resp) => {
      dispatch({
        type: reducerEvent,
        payload: {
          resp: resp
        }
      });
    });
  }
};
