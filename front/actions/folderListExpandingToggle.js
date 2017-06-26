export default function folderListExpandingToggle(payload) {
    return function(dispatch) {
        dispatch({
            type: 'FOLDER_LIST_EXPANDING_TOGGLE',
            payload: payload
        });
    }
};
