import './tile-item.sass';

import React from 'react';

export default function TileItem (props) {
  return (
    <div className='realtime-calibration-tile-item'>
      <div className='realtime-calibration-tile-item__box'>
        <div className='realtime-calibration-tile-item__params-colorbox'
          style={{ backgroundColor: ('#' + props.color) }}
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
            { props.value }
          </div>
        </div>
      </div>
    </div>
  );
}
