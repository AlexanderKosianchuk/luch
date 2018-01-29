import './params-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import TileItem from 'components/realtime-calibration/tile-item/TileItem';

class ParamsContainer extends Component {
  buildTile() {
    let ap = this.props.realtimeCalibrationParams.containerAnalogParams;
    if (ap.length === 0) {
        return <Translate value='realtimeCalibration.paramsContainer.chooseParams'/>;
    }

    let newFrame = new Array(ap.length);
    if (this.props.data.length > 0) {
      newFrame = this.props.data[this.props.data.length - 1];
    }

    return ap.map((item, index) => {
      console.log(item.id, newFrame[item.id], newFrame);
      return (<TileItem
        key={ index }
        value={ newFrame[item.id] || 0 }
        paramColor={ item.color }
        name={ item.name }
        code={ item.code }
      />);
    });
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
