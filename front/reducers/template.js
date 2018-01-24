const initialState = {
  pending: null,
  id: null,
  name: {},
  params: [],
  paramCodes: [],
  servicePurpose: {},
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
        id: action.payload.response.id,
        name: action.payload.response.name,
        params: action.payload.response.params,
        paramCodes: action.payload.response.paramCodes,
        servicePurpose: action.payload.response.servicePurpose,
      };
    default:
      return state;
  }
}
