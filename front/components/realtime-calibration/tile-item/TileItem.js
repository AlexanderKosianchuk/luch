import './tile-item.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import transmit from 'actions/transmit';

class TileItem extends Component {
  handleClick() {
    this.props.transmit('CHANGE_REALTIME_CALIBRATION_PARAM_CHECKSTATE', {
      ...this.props.param,
      ...{
        state: true,
        view: 'chart'
      }
    });
  }

  render() {
    return (
      <div className='realtime-calibration-tile-item'>
        <div className='realtime-calibration-tile-item__box'>
          <div className='realtime-calibration-tile-item__colorbox'
            style={{ backgroundColor: ('#' + this.props.param.color) }}
          >
          </div>
          <div className='realtime-calibration-tile-item__label'>
            <div className='realtime-calibration-tile-item__code'>
              { this.props.param.code }
            </div>
            <div className='realtime-calibration-tile-item__name'>
              { this.props.param.name }
            </div>
            <div className='realtime-calibration-tile-item__value'>
              { this.props.value }
            </div>
            { this.props.canChartDisplay &&
              <div
                className='realtime-calibration-tile-item__checkbox'
                onClick={ this.handleClick.bind(this) }
              >
                <span className='glyphicon glyphicon-facetime-video'></span>
              </div>
            }
          </div>
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {};
}

function mapDispatchToProps(dispatch) {
  return {
    transmit: bindActionCreators(transmit, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(TileItem);
