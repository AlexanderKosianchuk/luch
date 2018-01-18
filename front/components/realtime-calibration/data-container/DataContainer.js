import './data-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import RealtimeChart from 'components/realtime-calibration/realtime-chart/RealtimeChart';
import ParamsContainer from 'components/realtime-calibration/params-container/ParamsContainer';

import request from 'actions/request';
import bindRealtimeCalibrationSocketEvents from 'actions/socket/bindRealtimeCalibrationSocketEvents';

class DataContainer extends Component {
  componentWillReceiveProps(nextProps) {
    if ((nextProps.status === 'init')
      && (this.props.status !== nextProps.status)
    ) {
      this.props.bindRealtimeCalibrationSocketEvents({
        interactionUrl: this.props.interactionUrl,
        status: this.props.webSocketsStatus,
        uid: this.props.uid
      });
    }
  }

  buildHeader() {
    if (this.props.status === null) {
       return <Translate value='realtimeCalibration.dataContainer.configureConnection'/>;
    } else if (this.props.status === 'init') {
       return <Translate value='realtimeCalibration.dataContainer.init'/>;
    } else if (this.props.status === 'bindingSocket') {
       return <Translate value='realtimeCalibration.dataContainer.connectionPending'/>;
    } else if (this.props.status === 'waitingData') {
      return <Translate value='realtimeCalibration.dataContainer.waitingData'/>;
    } else if (this.props.status === 'onAir') {
      return null;
    }
  }

  render() {
    return (
      <div className='realtime-calibration-data-container'>
        <div className='realtime-calibration-data-container__label'>
          { this.buildHeader() }
        </div>
        <div className='realtime-calibration-data-container__output'>
          <div className='realtime-calibration-data-container__chart'>
            <RealtimeChart/>
          </div>
          <div className='realtime-calibration-data-container__params'>
            <ParamsContainer/>
          </div>
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    status: state.realtimeCalibrationData.status,
    errorCode: state.realtimeCalibrationData.errorCode,
    webSocketsStatus: state.webSockets.status
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    bindRealtimeCalibrationSocketEvents: bindActionCreators(bindRealtimeCalibrationSocketEvents, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(DataContainer);
