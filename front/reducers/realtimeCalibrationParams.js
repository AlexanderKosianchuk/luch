const initialState = {
  chosenChartAnalogParams: [],
  chosenChartBinaryParams: [],
  chosenContainerAnalogParams: [],
  chosenContainerBinaryParams: []
};

export default function realtimeCalibrationParams(state = initialState, action) {
  switch (action.type) {
    case 'CHANGE_FLIGHT_PARAM_CHECKSTATE':
      let chosenParams = [];

      if (action.payload.context === 'realtimeCalibrationChartParams') {
        if (action.payload.paramType === 'ap') {
          chosenParams = state.chosenChartAnalogParams;
        } else if (action.payload.paramType === 'bp') {
          chosenParams = state.chosenChartBinaryParams;
        }
      } else if (action.payload.context === 'realtimeCalibrationContainerParams') {
        if (action.payload.paramType === 'ap') {
          chosenParams = state.chosenContainerAnalogParams;
        } else if (action.payload.paramType === 'bp') {
          chosenParams = state.chosenContainerBinaryParams;
        }
      } else {
        return state;
      }

      let getIndexById = function (id, array) {
        let itemIndex = null;
        array.forEach((item, index) => {
          if (item.id === id) itemIndex = index;
        });

        return itemIndex; // or undefined
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
    default:
      return state;
  }
}
