export default function changeSelectedEndFrame(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_SELECTED_END_FRAME',
            payload: payload
        });
    }
};
