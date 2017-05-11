const initialState = {
    pending: null,
    report: []
};

export default function settlementsReport(state = initialState, action) {
    switch (action.type) {
        case 'APPLY_SETTLEMENTS_FILTER':
            state.pending = true;
            return { ...state };
        case 'REPORT_FETCHED':
            return { ... Object.assign(state, {
                pending: false,
                report: action.payload
            })};
        default:
            return state;
    }
}
