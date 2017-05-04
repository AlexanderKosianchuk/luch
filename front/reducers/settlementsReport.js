const initialState = {
    receiving: null,
    report: []
};

export default function settlementsReport(state = initialState, action) {
    switch (action.type) {
        case 'APPLY_SETTLEMENTS_FILTER':
            state.receiving = true;
            return { ...state };
        case 'REPORT_FETCHED':
            return { ... Object.assign(state, {
                receiving: false,
                report: action.payload
            })};
        default:
            return state;
    }
}
