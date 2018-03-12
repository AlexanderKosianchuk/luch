import './physics.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import TileItem from 'components/realtime-calibration/tile-item/TileItem';

class Physics extends Component {
  buildTile() {
    let ap = this.props.params.analog;

    let newFrame = new Array(ap.length);
    if (this.props.data.length > 0) {
      newFrame = this.props.data[this.props.data.length - 1];
    }

    return ap.map((item, index) => {
      return (<TileItem
        key={ index }
        value={ this.getValue(newFrame[item.id]) }
        param={ item }
        canChartDisplay={ true }
        onlyBinaryValue={ false }
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
          { (this.props.params.analog.length === 0) ? (
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
