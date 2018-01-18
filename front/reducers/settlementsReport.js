const initialState = {
  pending: null,
  report: []
};

export default function settlementsReport(state = initialState, action) {
  switch (action.type) {
    case 'GET_SETTLEMENTS_REPORT_START':
      state.pending = true;
      return { ...state };
    case 'GET_SETTLEMENTS_REPORT_COMPLETE':
      return { ... Object.assign(state, {
        pending: false,
        report: action.payload.response
      })};
    default:
      return state;
  }
}
