import './tile-item.sass';

import React from 'react';

export default function TileItem (props) {
    return (
        <div className='realtime-calibration-tile-item'>
            <div className='realtime-calibration-tile-item__box'>
                <div className='realtime-calibration-tile-item__colorbox'
                    style={{ backgroundColor: ('#' + props.paramColor) }}
                >
                </div>
                <div className='realtime-calibration-tile-item__label'>
                    <div className='realtime-calibration-tile-item__code'>
                        { props.code }
                    </div>
                    <div className='realtime-calibration-tile-item__name'>
                        { props.name }
                    </div>
                    <div className='realtime-calibration-tile-item__value'>
                        { Number((props.value).toFixed(2)) }
                    </div>
                </div>
            </div>
        </div>
    );
}
