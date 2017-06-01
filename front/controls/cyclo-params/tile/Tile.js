import React from 'react';

import Item from 'controls/cyclo-params/item/Item';

export default function Tile(props){
    function buildParams(params, colorPickerEnabled)
    {
        let items = [];

        if (params
            && (params.length > 0)
        ) {
            params.forEach((item, index) => {
                items.push(<Item
                    key={ index }
                    param={ item }
                    fdrId={ props.fdrId }
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
