import './binary.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import TileItem from 'components/realtime-calibration/tile-item/TileItem';

class Binary extends Component {
  buildTile() {
    let bp = this.props.params.containerBinaryParams;

    let newFrame = new Array(bp.length);
    if (this.props.data.length > 0) {
      newFrame = this.props.data[this.props.data.length - 1];
    }

    return bp.map((item, index) => {
      return (<TileItem
        key={ index }
        value={ this.getValue(newFrame[item.id]) }
        color={ item.color }
        name={ item.name }
        code={ item.code }
      />);
    });
  }

  render() {
    return (
      <div className='realtime-calibration-binary'>
        <div className='realtime-calibration-binary__header'>
          { (this.props.params.containerBinaryParams.length > 0) && (
            <Translate value='realtimeCalibration.binary.header' />
          )}
        </div>
        <div className='realtime-calibration-binary__container'>
          { this.buildTile() }
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    currentFrame: state.realtimeCalibrationData.currentFrame,
    data: state.realtimeCalibrationData.binary,
    params: state.realtimeCalibrationParams
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Binary);
