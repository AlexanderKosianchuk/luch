import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import ee from 'event-emitter';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/calibration-form/toolbar/Toolbar';
import Form from 'components/calibration-form/form/Form';

import request from 'actions/request';

class CalibrationForm extends Component {
  constructor(props) {
    super(props);

    let emitter = ee();
    this.submit = (event, name) => emitter.emit('calibration-form-submit', name);
    this.onSubmit = (callback) => emitter.on('calibration-form-submit', callback);
    this.offSubmit = () => emitter.off('calibration-form-submit');
  }

  componentDidMount() {
    if (this.props.action === 'update') {
      this.props.request(
        ['calibration', 'getCalibrationById'],
        'get',
        'CALIBRATION',
        { id: this.props.calibrationId }
      );
    }

    if ((this.props.action === 'create')
      && (this.props.cycloFdrId !== this.props.fdrId)
    ) {
      this.props.request(
        ['calibration', 'getCalibrationParams'],
        'get',
        'CALIBRATION_PARAMS',
        { fdrId: this.props.fdrId }
      );
    }
  }

  shouldComponentUpdate(nextProps, nextState) {
    if (nextProps.pending !== this.props.pending) {
      return false;
    }

    return true;
  }

  render() {
    return (
      <div>
        <Menu />
        <Toolbar
          action={ this.props.action }
          submit={ this.submit }
        />
        <Form
          fdrId={ this.props.fdrId }
          calibrationId={ this.props.calibrationId }
          onSubmit={ this.onSubmit }
          offSubmit={ this.offSubmit }
        />
      </div>
    );
  }
}

function mapStateToProps(state, ownProps) {
  return {
    pending: state.calibrations.pending,
    fdrId: parseInt(ownProps.match.params.fdrId) || null,
    calibrationId: parseInt(ownProps.match.params.calibrationId) || null,
    action: (ownProps.location.pathname.indexOf('update') > -1) ? 'update' : 'create'
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(CalibrationForm);
