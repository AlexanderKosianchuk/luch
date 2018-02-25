import './binary-tile-item.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import transmit from 'actions/transmit';

class BinaryTileItem extends Component {
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
      <div className='realtime-calibration-binary-tile-item'>
        <div className={ 'realtime-calibration-binary-tile-item__box ' +
          (
            (this.props.param.value === true)
            ? 'realtime-calibration-binary-tile-item__box--active'
            : ''
          )
        }
        >
          <div className='realtime-calibration-binary-tile-item__colorbox'
            style={{ backgroundColor: ('#' + this.props.param.color) }}
          >
          </div>
          <div className='realtime-calibration-binary-tile-item__label'>
            <div className='realtime-calibration-binary-tile-item__code'>
              { this.props.param.code }
            </div>
            <div className='realtime-calibration-binary-tile-item__name'>
              { this.props.param.name }
            </div>
            <div
              onClick={ this.handleClick.bind(this) }
              className='realtime-calibration-binary-tile-item__checkbox'
            >
              <span className='glyphicon glyphicon-facetime-video'></span>
            </div>
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

export default connect(mapStateToProps, mapDispatchToProps)(BinaryTileItem);
