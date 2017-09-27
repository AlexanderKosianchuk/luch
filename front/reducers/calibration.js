const initialState = {
    pending: null,

    fdrId: null,
    fdrName: null,
    userId: null,

    id: null,
    name: null,
    dtCreated: null,
    dtUpdated: null,

    params: []
};

/*
* This reducer user for create or edit calibration form
* In create case calibration info is null
*/
export default function calibration(state = initialState, action) {
    switch (action.type) {
        case 'GET_CALIBRATION_START':
            return { ...state,
                ...{ pending: true }
            };
        case 'GET_CALIBRATION_COMPLETE':
            return { ...state, ...{
                pending: false,

                fdrId: action.payload.response.fdrId || null,
                fdrName: action.payload.response.fdrName || null,
                userId: action.payload.response.userId || null,

                id: action.payload.response.id || null,
                name: action.payload.response.name || null,
                dtCreated:action.payload.response.dtCreated || null,
                dtUpdated: action.payload.response.dtUpdated || null,

                params: action.payload.response.params || []
            }};
        default:
            return state;
    }
}
