export default function changeSelectedStartFrame(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_SELECTED_START_FRAME',
            payload: payload
        });
    }
};
