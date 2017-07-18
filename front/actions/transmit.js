export default function transmit(event, payload = {}) {
    return (dispatch) => {
        dispatch({
            type: event,
            payload: payload
        });
    }
};
