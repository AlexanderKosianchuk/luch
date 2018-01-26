const initialState = {
  chartAnalogParams: [],
  chartBinaryParams: [],
  containerAnalogParams: [],
  containerBinaryParams: []
};

export default function realtimeCalibrationParams(state = initialState, action) {
  switch (action.type) {
    case 'CHANGE_REALTIME_CALIBRATION_PARAM_CHECKSTATE':
      let chosenParams = [];

      if (action.payload.type === 'ap') {
        chosenParams = state.containerAnalogParams;
      } else if (action.payload.type === 'bp') {
        chosenParams = state.containerBinaryParams;
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
        chosenParams.push(action.payload);
      }

      return { ...state };
    case 'CLEAR_REALTIME_CALIBRATION_PARAMS':
      return initialState;
    default:
      return state;
  }
}
