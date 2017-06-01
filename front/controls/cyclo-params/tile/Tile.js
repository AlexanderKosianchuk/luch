import React from 'react';

import Item from 'controls/cyclo-params/item/Item';

export default function Tile(props){
    function buildParams(params)
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
                />);
            });
        }

        return items;
    }

    return <div className='cyclo-params-tile container-fluid'>
        <div className='row'>
            <div className='col-xs-6'>
                { buildParams(props.analogParams) }
            </div>
            <div className='col-xs-6'>
                { buildParams(props.binaryParams) }
            </div>
        </div>
    </div>;
}
