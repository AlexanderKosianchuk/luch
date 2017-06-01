import React from 'react';

import Item from 'controls/cyclo-params/item/Item';

export default function Tile(props) {
    let checkedAnalogParamIds = [];
    let checkedBinaryParamIds = [];

    props.checkedAnalogParams.forEach((item) => {
        checkedAnalogParamIds.push(item.id);
    });

    props.checkedBinaryParams.forEach((item) => {
        checkedBinaryParamIds.push(item.id);
    });

    function buildParams(params, colorPickerEnabled)
    {
        let items = [];

        if (params
            && (params.length > 0)
        ) {
            params.forEach((item, index) => {
                let isChosen = false;
                if (((item.type === 'ap')
                    && (checkedAnalogParamIds.indexOf(item.id) >= 0))
                    || ((item.type === 'bp')
                    && (checkedBinaryParamIds.indexOf(item.id) >= 0))
                ) {
                    isChosen = true;
                }

                items.push(<Item
                    key={ index + item.type }
                    param={ item }
                    isChosen={ isChosen }
                    flightId={ props.flightId }
                    colorPickerEnabled={ colorPickerEnabled }
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
