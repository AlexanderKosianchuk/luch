import './vertical-toolbar.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';

import FdrSelector from 'controls/fdr-selector/FdrSelector';
import CalibrationSelector from 'controls/calibration-selector/CalibrationSelector';

import ChooseParamsButtons from 'components/realtime-calibration/choose-params-buttons/ChooseParamsButtons';

import transmit from 'actions/transmit';
import request from 'actions/request';
import redirect from 'actions/redirect';

class VerticalToolbar extends Component {
  static form = null;

  constructor(props) {
    super(props);

    this.state = {
      fakeData: true,
      sources: ['192.168.11.1:2017']
    }
  }

  componentWillUnmount() {
    if (this.props.isRunning !== null) {
      let data = this.gatherInteractionData();

      this.props.request(
        this.props.interactionUrl+'/realtimeCalibration/stopUdp',
        'post',
        'REALTIME_CALIBRATION_BREAK',
        data
      );
    }
  }

  handleStartClick(event) {
    event.preventDefault();

    if (this.props.isRunning === null) {
      let data = this.gatherInteractionData();

      this.props.request(
        this.props.interactionUrl+'/realtimeCalibration/startUdp',
        'post',
        'REALTIME_CALIBRATION_RECEIVING',
        data
      );
    }
  }

  handlePauseClick(event) {
    event.preventDefault();

    if (this.props.isRunning === true) {
      let data = this.gatherInteractionData();

      this.props.request(
        this.props.interactionUrl+'/realtimeCalibration/pauseUdp',
        'post',
        'REALTIME_CALIBRATION_FREEZE',
        data
      );
    }
  }

  handleResumeClick(event) {
    event.preventDefault();

    if (this.props.isRunning === false) {
      let data = this.gatherInteractionData();

      this.props.request(
        this.props.interactionUrl+'/realtimeCalibration/startUdp',
        'post',
        'REALTIME_CALIBRATION_RECEIVING',
        data
      );
    }
  }

  buildIpsInputs() {
    let ips = [];

    for (let ii = 0; ii < this.state.sources.length; ii++) {
      ips.push(<input
        key={ ii }
        className='form-control'
        name='ip[]'
        value={ this.state.sources[ii] }
        onChange={ this.handleChange.bind(this, ii) }
        />
      );
    }

    return ips;
  }

  handleChange(index, event) {
    let sources = this.state.sources.slice();
    sources[index] = event.target.value;

    this.setState({ sources: sources });
  }

  handleAddSourceClick(event) {
    event.preventDefault();

    let sources = this.state.sources.slice();
    sources.push('');

    this.setState({ sources: sources });
  }

  gatherInteractionData() {
    let fdrId = null;
    let calibrationId = null;

    let ipInputs = this.form.querySelectorAll('input[name="ip[]"]');
    let ips = [];

    ipInputs.forEach((item) => {
      if (item.value.length >= 7) {
        ips.push(item.value);
      }
    });

    if (ips.length === 0) {
      alert(I18n.t('realtimeCalibration.verticalToolbar.enterIpToConnect'));
      return;
    }

    var data = new FormData();
    data.append('uid', this.props.uid);
    data.append('fdrId', this.props.chosenFdr.id);
    data.append('calibrationId', this.props.chosenCalibration.id);
    data.append('ips', ips);
    data.append('fakeData', this.state.fakeData);
    data.append('cors', window.location.hostname);

    return data;
  }

  handleFakeDataClick() {
    this.setState({ fakeData: !this.state.fakeData });
  }

  handleSaveClick() {
    //88ac42ed2685402f/fdr-id/1/calibration-id/9
    this.props.uid;
    this.props.chosenFdr.id;
    this.props.chosenCalibration.id;

    let url = '/uploading/'
      + this.props.uid
      + '/fdr-id/'
      + this.props.chosenFdr.id;

    if (this.props.chosenCalibration
        && this.props.chosenCalibration.id
    ) {
      url += '/calibration-id/'
        + this.props.chosenCalibration.id;
    }

    this.props.redirect(url);
  }

  putStateButton() {
    if (this.props.isRunning === true) {
      return (<button
        className='btn btn-default'
        onClick={ this.handlePauseClick.bind(this) }
      >
        <Translate value='realtimeCalibration.verticalToolbar.stop'/>
      </button>);
    } else if (this.props.isRunning === false) {
      return (
        <div>
          <button
            className='realtime-calibration-vertical-toolbar__btn btn btn-default'
            onClick={ this.handleResumeClick.bind(this) }
          >
            <Translate value='realtimeCalibration.verticalToolbar.start'/>
          </button>
          <button
            className='realtime-calibration-vertical-toolbar__btn btn btn-default'
            onClick={ this.handleSaveClick.bind(this) }
          >
            <Translate value='realtimeCalibration.verticalToolbar.save'/>
          </button>
        </div>
      );
    }

    return (<button
      className='btn btn-default'
      onClick={ this.handleStartClick.bind(this) }
    >
      <Translate value='realtimeCalibration.verticalToolbar.start'/>
    </button>);
  }

  render() {
    return (
      <form
        className='realtime-calibration-vertical-toolbar'
        ref={ (form) => { this.form = form; } }
      >
        <div className='realtime-calibration-vertical-toolbar__connection-params'>
          <Translate value='realtimeCalibration.verticalToolbar.connectionParams'/>
        </div>
        <div className='realtime-calibration-vertical-toolbar__label'>
          <Translate value='realtimeCalibration.verticalToolbar.fdrType'/>
        </div>
        <div>
          <ul className='realtime-calibration-vertical-toolbar__fdr-type'>
            <FdrSelector chosenFdrId={ this.props.fdrId } />
            <CalibrationSelector/>
          </ul>
        </div>
        <div className='realtime-calibration-vertical-toolbar__label'>
          <Translate value='realtimeCalibration.verticalToolbar.connectionType'/>
        </div>
        <div className='realtime-calibration-vertical-toolbar__controll'>
          <select className='form-control' name='connectionType'>
            <option value='udp'>UDP</option>
          </select>
        </div>
        <div className='realtime-calibration-vertical-toolbar__label'>
          <Translate value='realtimeCalibration.verticalToolbar.sourceIps'/>
        </div>
        <div className='realtime-calibration-vertical-toolbar__controll'>
          { this.buildIpsInputs() }
        </div>
        <div className='realtime-calibration-vertical-toolbar__button'>
          <button
            className='btn btn-default'
            onClick={ this.handleAddSourceClick.bind(this) }
          >
            <Translate value='realtimeCalibration.verticalToolbar.addSource'/>
          </button>
        </div>
        <ChooseParamsButtons
          fdrId={ this.props.fdrId }
          paramsSource={ this.props.paramsSource }
          fdrTemplateId={ this.props.fdrTemplateId }
          changeParamsSource={ this.props.changeParamsSource }
        />
        <div className='realtime-calibration-vertical-toolbar__inline-label-container'>
          <div className='realtime-calibration-vertical-toolbar__inline-label'>
            <Translate value='realtimeCalibration.verticalToolbar.fakeData'/>
          </div>
          <div className='realtime-calibration-vertical-toolbar__inline-label'>
            <input className='form-control realtime-calibration-vertical-toolbar__checkbox' type='checkbox'
              onChange={ this.handleFakeDataClick.bind(this) }
              checked={ (this.state.fakeData === true) ? 'checked' : '' }
            />
          </div>
        </div>
        <div className='realtime-calibration-vertical-toolbar__button'>
          { this.putStateButton() }
        </div>
      </form>
    );
  }
}

function mapStateToProps(state) {
  return {
    isRunning: state.realtimeCalibrationData.status,
    interactionUrl: state.appConfig.config.interactionUrl,
    chosenFdr: state.fdrs.chosen,
    chosenCalibration: state.calibrations.chosen,
  };
}

function mapDispatchToProps(dispatch) {
  return {
    transmit: bindActionCreators(transmit, dispatch),
    request: bindActionCreators(request, dispatch),
    redirect: bindActionCreators(redirect, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(VerticalToolbar);
