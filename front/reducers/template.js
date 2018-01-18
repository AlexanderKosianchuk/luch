const initialState = {
  pending: null,
  name: {},
  servisePurpose: {},
  ap: {},
  bp: {}
};

export default function template(state = initialState, action) {
  switch (action.type) {
    case 'GET_TEMPLATE_START':
      return {
        ...state,
        ...{ pending: true }
      };
    case 'GET_TEMPLATE_COMPLETE':
      return {
        pending: false,
        name: action.payload.response.name,
        servisePurpose: action.payload.response.servisePurpose,
        ap: action.payload.response.ap,
        bp: action.payload.response.bp
      };
    default:
      return state;
  }
}
