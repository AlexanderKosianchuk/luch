const initialState = {
  pending: null,
  fdrId: null,
  analogParams: [],
  binaryParams: [],
  chosenAnalogParams: [],
  chosenBinaryParams: []
};

export default function fdrCyclo(state = initialState, action) {
  switch (action.type) {
    case 'GET_FDR_CYCLO_START':
      return { ...state, ...{ pending: true }};
    case 'GET_FDR_CYCLO_COMPLETE':
      return { ...state,
        ...{
          pending: false,
          fdrId: action.payload.response.fdrId,
          analogParams: action.payload.response.analogParams,
          binaryParams: action.payload.response.binaryParams,
        }
      };
    case 'CHANGE_FLIGHT_PARAM_CHECKSTATE':
      if (action.payload.context !== 'flightParams') {
        return state;
      }

      let getIndexById = function (id, array) {
        let itemIndex = null;
        array.forEach((item, index) => {
          if (item.id === id) itemIndex = index;
        });

        return itemIndex; // or undefined
      }

      let chosenParams = [];
      if (action.payload.paramType === 'ap') {
        chosenParams = state.chosenAnalogParams;
      } else if (action.payload.paramType === 'bp') {
        chosenParams = state.chosenBinaryParams;
      }

      let itemIndex = getIndexById (
        action.payload.id,
        chosenParams
      );

      if ((action.payload.state === false)
        && (itemIndex !== null)
      ) {
        chosenParams.splice(itemIndex, 1);
      }

      if ((action.payload.state === true)
        && (itemIndex === null)
      ) {
        chosenParams.push({
          id: action.payload.id,
          paramType: action.payload.paramType
        });
      }

      return { ...state };
    case 'SET_CHECKED_FLIGHT_PARAMS':
      return {
        ...state,
        ...{
          chosenAnalogParams: action.payload.ap,
          chosenBinaryParams: action.payload.bp
        }
      };
    default:
      return state;
  }
}
