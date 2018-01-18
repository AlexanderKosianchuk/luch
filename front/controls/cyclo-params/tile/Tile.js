import React from 'react';

import Item from 'controls/cyclo-params/item/Item';

export default function Tile(props) {
  let chosenAnalogParamIds = [];
  let chosenBinaryParamIds = [];

  props.chosenAnalogParams.forEach((item) => {
    chosenAnalogParamIds.push(item.id);
  });

  props.chosenBinaryParams.forEach((item) => {
    chosenBinaryParamIds.push(item.id);
  });

  function buildParams(params, colorPickerEnabled) {
    let items = [];

    if (params
      && (params.length > 0)
    ) {
      params.forEach((item, index) => {
        let isChosen = false;
        if (((item.type === 'ap')
          && (chosenAnalogParamIds.indexOf(item.id) >= 0))
          || ((item.type === 'bp')
          && (chosenBinaryParamIds.indexOf(item.id) >= 0))
        ) {
          isChosen = true;
        }

        items.push(<Item
          key={ index + item.type }
          param={ item }
          isChosen={ isChosen }
          flightId={ props.flightId }
          colorPickerEnabled={ colorPickerEnabled }
          context={ props.context }
        />);
      });
    }

    return items;
  }

  return <div className='cyclo-params-tile container-fluid'>
    <div className='row'>
      <div className='col-xs-6'>
        { buildParams(props.analogParams, props.colorPickerEnabled) }
      </div>
      <div className='col-xs-6'>
        { buildParams(props.binaryParams, props.colorPickerEnabled) }
      </div>
    </div>
  </div>;
}
