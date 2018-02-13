import './data-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import RealtimeChart from 'components/realtime-calibration/realtime-chart/RealtimeChart';
import Physics from 'components/realtime-calibration/physics/Physics';
import Events from 'components/realtime-calibration/events/Events';
import Binary from 'components/realtime-calibration/binary/Binary';

import request from 'actions/request';
import bindSocketEvent from 'actions/bindSocketEvent';

class DataContainer extends Component {
  componentDidMount() {
    if (this.props.status === true) {
      this.bindSocketEvent();
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.status === true) {
      this.bindSocketEvent();
    }
  }

  bindSocketEvent() {
    this.props.bindSocketEvent({
      io: this.props.io,
      ioEvent: 'newData',
      bindedEvents: this.props.bindedEvents,
      registerUrl: this.props.appConfig.interactionUrl+'/realtimeCalibration/register?uid='+ this.props.uid,
      reducerEvent: 'RECEIVED_REALTIME_CALIBRATING_NEW_FRAME'
    });
  }

  render() {
    return (
      <div className='realtime-calibration-data-container'>
        <div className='realtime-calibration-data-container__output'>
          <div className='realtime-calibration-data-container__events'>
            <Events/>
          </div>
          <div className='realtime-calibration-data-container__params'>
            <Physics/>
          </div>
          <div className='realtime-calibration-data-container__binary'>
            <Binary/>
          </div>
          <div className='realtime-calibration-data-container__chart'>
            <RealtimeChart/>
          </div>
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    status: state.webSockets.status,
    io: state.webSockets.io,
    appConfig: state.appConfig.config,
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
