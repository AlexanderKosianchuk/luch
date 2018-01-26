export default function transmit(event, payload = {}) {
  return (dispatch) => {
    return new Promise((resolve, reject) => {
      dispatch({
        type: event,
        payload: payload
      });
      resolve(payload);
    });
  }
};
