import _isInteger from 'lodash.isinteger';

const initialState = [];

export default function flightUploadingState(state = initialState, action) {
    function findUploadIndex (uploads, uploadingUid) {
        let progressChangedItem = null;
        uploads.find((element, index, array) => {
            if (element.uploadingUid === uploadingUid) {
                progressChangedItem = index;
            }
        });

        return progressChangedItem;
    }

    switch (action.type) {
        case 'START_FLIGHT_UPLOADING': {
            state.push({
                uploadingUid: action.payload.uploadingUid,
                progress: 0
            });
            return [ ...state ];
        }
        case 'FLIGHT_UPLOADING_PROGRESS_CHANGE': {
            let progressChangedItem = findUploadIndex (state, action.payload.uploadingUid);

            if (progressChangedItem !== null) {
                state[progressChangedItem].progress = action.payload.progress;
            } else {
                state.push(action.payload);
            }

            return [ ...state ];
        }
        case 'FLIGHT_UPLOADING_COMPLETE': {
            let uploadingIndex = findUploadIndex (state, action.payload.uploadingUid);

            if (_isInteger(uploadingIndex)) {
                state.splice(uploadingIndex, 1);
                return [ ...state ];
            }

            return state;
        }
        default:
            return state;
    }
}
