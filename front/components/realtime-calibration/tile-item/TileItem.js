import './tile-item.sass';

import React, { Component } from 'react';

import ChartGroupIndicator from 'components/realtime-calibration/chart-group-indicator/ChartGroupIndicator';

export default function TileItem (props) {
  return (
    <div className='realtime-calibration-tile-item'>
      <div className={ 'realtime-calibration-tile-item__box ' +
        (
          ((props.value === true) && props.onlyBinaryValue)
          ? 'realtime-calibration-tile-item__box--active'
          : ''
        )
      }
      >
        <div className='realtime-calibration-tile-item__colorbox'
          style={{ backgroundColor: ('#' + props.param.color) }}
        >
        </div>
        <div className='realtime-calibration-tile-item__container'>
          <div className='realtime-calibration-tile-item__code'>
            { props.param.code }
          </div>
          <div className='realtime-calibration-tile-item__name'>
            { props.param.name }
          </div>
          { !props.onlyBinaryValue &&
            <div className='realtime-calibration-tile-item__value'>
              { props.value }
            </div>
          }
        </div>
        { props.canChartDisplay &&
          <ChartGroupIndicator param={ props.param }/>
        }
      </div>
    </div>
  );
}
