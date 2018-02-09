import './physics.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import TileItem from 'components/realtime-calibration/tile-item/TileItem';

class Physics extends Component {
  buildTile() {
    let ap = this.props.params.containerAnalogParams;

    let newFrame = new Array(ap.length);
    if (this.props.data.length > 0) {
      newFrame = this.props.data[this.props.data.length - 1];
    }

    return ap.map((item, index) => {
      return (<TileItem
        key={ index }
        value={ this.getValue(newFrame[item.id]) }
        color={ item.color }
        name={ item.name }
        code={ item.code }
      />);
    });
  }

  getValue (value) {
    if (value) {
      return Number((value).toFixed(2))
    }

    return 0;
  }

  render() {
    return (
      <div className='realtime-calibration-physics'>
        <div className='realtime-calibration-physics__header'>
          { (this.props.params.containerAnalogParams.length === 0) ? (
            <Translate value='realtimeCalibration.physics.chooseParams'/>
          ) : (
            <Translate value='realtimeCalibration.physics.header' />
          )}
        </div>
        <div className='realtime-calibration-physics__container'>
          { this.buildTile() }
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    currentFrame: state.realtimeCalibrationData.currentFrame,
    data: state.realtimeCalibrationData.phisics,
    params: state.realtimeCalibrationParams
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Physics);
