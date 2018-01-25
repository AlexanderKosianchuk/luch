const initialState = {
  pending: null,
  chosenItems: [],
  items: [],
};

export default function fdrTemplates(state = initialState, action) {
  switch (action.type) {
    case 'GET_FDR_TEMPLATES_START':
      return {
        ...state,
        ...{ pending: true }
      };
    case 'GET_FDR_TEMPLATES_COMPLETE':
      return {
        ...state, ...{
          pending: false,
          items: action.payload.response
      }};
    case 'CHOOSE_FDR_TEMPLATE':
      var chosenIndex = state.items.findIndex((item) => {
        return item.id === action.payload.id;
      });

      if (chosenIndex === -1) {
        console.warn('Chosen unexist template. Id: ' + action.payload.id);
        return state;
      }

      return {
        ...state,
        ...{ chosenItems: [state.items[chosenIndex]] }
      };
    case 'FDR_TEMPLATE_CHOSEN':
      var indexToChoose = state.items.findIndex((item) => {
        return item.id === action.payload.id;
      });

      if (indexToChoose === -1) {
        console.warn('Chosen unexist template. Id: ' + action.payload.id);
        return state;
      }

      var indexInChosen = state.chosenItems.findIndex((item) => {
        return item.id === action.payload.id;
      });

      // already chosen
      if (indexInChosen !== -1) {
        return state;
      }

      state.chosenItems.push(state.items[indexToChoose])
      return {
        ...state,
      };
    case 'FDR_TEMPLATE_UNCHOSEN':
      var indexToUnchoose = state.chosenItems.findIndex((item) => {
        return item.id === action.payload.id;
      });

      if (indexToChoose === -1) {
        return state;
      }

      state.chosenItems.splice(indexToUnchoose, 1);
      return {
        ...state
      };
    default:
      return state;
  }
}
