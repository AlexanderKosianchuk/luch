export default function toggleEventsSection(payload) {
    return function(dispatch) {
        dispatch({
            type: 'TOGGLE_EVENTS_SECTION',
            payload: payload
        });
    }
};
