import './binary-tile-item.sass';

import React from 'react';

export default function BinaryTileItem (props) {
  return (
    <div className='realtime-calibration-binary-tile-item'>
      <div className={ 'realtime-calibration-binary-tile-item__box ' +
        (
          (props.value === true)
          ? 'realtime-calibration-binary-tile-item__box--active'
          : ''
        )
      }
      >
        <div className='realtime-calibration-binary-tile-item__colorbox'
          style={{ backgroundColor: ('#' + props.color) }}
        >
        </div>
        <div className='realtime-calibration-binary-tile-item__label'>
          <div className='realtime-calibration-binary-tile-item__code'>
            { props.code }
          </div>
          <div className='realtime-calibration-binary-tile-item__name'>
            { props.name }
          </div>
        </div>
      </div>
    </div>
  );
}
