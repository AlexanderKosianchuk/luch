const initialState = {
  pending: null,
  chosenItems: [],
  items: [],
};

export default function flightTemplates(state = initialState, action) {
  switch (action.type) {
    case 'GET_FLIGHT_TEMPLATES_START':
      return {
        ...state,
        ...{ pending: true }
      };
    case 'GET_FLIGHT_TEMPLATES_COMPLETE':
      return {
        ...state, ...{
          pending: false,
          items: action.payload.response
      }};
    case 'DELETE_TEMPLATE_START':
      return {
        ...state,
        ...{ pending: true }
      };
    case 'DELETE_TEMPLATE_COMPLETE':
      let newItems = [];
      state.items.forEach((item) => {
        if (item.name !== action.payload.request.templateName) {
          newItems.push(item);
        }
      });
      return {
        ...state, ...{
          pending: false,
          items: newItems
      }};
    case 'CHOOSE_TEMPLATE':
      return {
        ...state,
        ...{ chosenItems: [action.payload.name] }
      };
    case 'TEMPLATE_CHOSEN':
      if (state.chosenItems.indexOf(action.payload.name) === -1) {
        state.chosenItems.push(action.payload.name)
        return {
          ...state,
        };
      }
      return state;
    case 'TEMPLATE_UNCHOSEN':
      var index = state.chosenItems.indexOf(action.payload.name);
      state.chosenItems.splice(index, 1);
      return {
        ...state
      };
    default:
      return state;
  }
}
