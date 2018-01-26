import './params-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import TileItem from 'components/realtime-calibration/tile-item/TileItem';

class ParamsContainer extends Component {
  buildTile() {
    let analogParamsTile = [];

    this.props.realtimeCalibrationParams
      .containerAnalogParams
      .forEach((item, index) => {
        let value = 0;
        let frame = [];

        analogParamsTile.push(<TileItem
          key={ index }
          value={ value }
          paramColor={ item.color }
          name={ item.name }
          code={ item.code }
        />);
      });

    return analogParamsTile;
  }

  render() {
    return (
      <div className='realtime-calibration-params-container'>
        { this.buildTile() }
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    currentFrame: state.realtimeCalibrationData.currentFrame,
    data: state.realtimeCalibrationData.data,
    realtimeCalibrationParams: state.realtimeCalibrationParams
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ParamsContainer);
