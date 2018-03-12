const initialState = {
  analog: [],
  binary: []
};

export default function realtimeCalibrationParams(state = initialState, action) {
  switch (action.type) {
    case 'CHANGE_REALTIME_CALIBRATION_PARAM_CHECKSTATE':
      let key = null;

      if (action.payload.type === 'ap') {
        key = 'analog';
      } else if (action.payload.type === 'bp') {
        key = 'binary';
      }

      if (key === null) {
        throw Error('Invalid key passed in realtimeCalibrationParams');
      }

      let chosenParams = state[key].slice(0);

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

      return { ...state,
        ...{ [key]: chosenParams }
      };
    case 'CLEAR_REALTIME_CALIBRATION_PARAMS':
      return initialState;
    default:
      return state;
  }
}
