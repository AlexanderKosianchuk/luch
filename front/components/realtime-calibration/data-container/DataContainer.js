import './data-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import RealtimeChart from 'components/realtime-calibration/realtime-chart/RealtimeChart';
import ParamsContainer from 'components/realtime-calibration/params-container/ParamsContainer';

import request from 'actions/request';
import bindSocketEvent from 'actions/bindSocketEvent';

class DataContainer extends Component {
  componentWillReceiveProps(nextProps) {
    if (nextProps.status === true) {
      this.props.bindSocketEvent({
        io: this.props.io,
        ioEvent: 'newData',
        bindedEvents: this.props.bindedEvents,
        registerUrl: this.props.appConfig.interactionUrl+'/realtimeCalibration/register?uid='+ this.props.uid,
        reducerEvent: 'RECEIVED_REALTIME_CALIBRATING_NEW_FRAME'
      });
    }
  }

  buildHeader() {
    if (this.props.status === null) {
      return <Translate value='realtimeCalibration.dataContainer.configureConnection'/>;
    }

    return null;
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
    appConfig: state.appConfig.config,
    status: state.realtimeCalibrationData.status,
    io: state.webSockets.io,
    bindedEvents: state.webSockets.bindedEvents,
    errorCode: state.realtimeCalibrationData.errorCode,
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    bindSocketEvent: bindActionCreators(bindSocketEvent, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(DataContainer);
