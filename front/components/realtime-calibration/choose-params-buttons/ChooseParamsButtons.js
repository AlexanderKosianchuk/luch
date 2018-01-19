import './choose-params-buttons.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';
import Switch from 'react-bootstrap-switch';

import TemplateSelector from 'controls/template-selector/TemplateSelector';
import CycloParams from 'controls/cyclo-params/CycloParams';
import Dialog from 'controls/dialog/Dialog';

const CYCLO_CONTAINER_PARAMS_CONTEXT = 'realtimeCalibrationContainerParams';

class ChooseParamsButtons extends Component {
  constructor(props) {
    super(props);

    this.state = {
      paramsSource: 'template',
      containerParamsDialogShown: false
    }
  }

  handleContainerParamsDialogToggle(event) {
    event.preventDefault();

    this.setState({ containerParamsDialogShown: !this.state.containerParamsDialogShown });
  }

  buildContainerParamsDialogBody() {
    return <CycloParams
      fdrId={ this.props.fdrId }
      context={ CYCLO_CONTAINER_PARAMS_CONTEXT }
      chosenAnalogParams={ this.props.realtimeCalibrationParams.chosenContainerAnalogParams }
      chosenBinaryParams={ this.props.realtimeCalibrationParams.chosenContainerBinaryParams }
    />
  }

  buildContainerParamsDialogFooter() {
    return <button type="button" className="btn btn-default"
      onClick={ this.handleContainerParamsDialogToggle.bind(this) }
    >
      <Translate value='realtimeCalibration.chooseParamsButtons.apply'/>
    </button>;
  }

  handleSwitch(elem, state) {
    if (state === true) {
      this.setState({ paramsSource: 'template' });
    } else {
      this.setState({ paramsSource: 'manual' });
    }
  }

  render() {
    return (
      <div className='realtime-calibration-choose-params-buttons'>
        <div>
          <Translate value='realtimeCalibration.chooseParamsButtons.chooseSource'/>
          <Switch
            bsSize='small'
            onText={ I18n.t('realtimeCalibration.chooseParamsButtons.template') }
            offText={ I18n.t('realtimeCalibration.chooseParamsButtons.manual') }
            onChange={(el, state) => this.handleSwitch(el, state)}
            name='paramsSource'
          />
        </div>
          { (this.state.paramsSource === 'template') ? (
            <TemplateSelector
              fdrId={ this.props.fdrId }
            />
          ) : (
            <div>
              <button
                className='btn btn-default realtime-calibration-choose-params-buttons__button'
                onClick={ this.handleContainerParamsDialogToggle.bind(this) }
              >
                <Translate value='realtimeCalibration.chooseParamsButtons.containerParams'/>
              </button>
              <Dialog
                isShown={ this.state.containerParamsDialogShown }
                handleClose={ this.handleContainerParamsDialogToggle.bind(this) }
                buildTitle={ () => { return I18n.t('realtimeCalibration.chooseParamsButtons.chooseParamsToShowInContainer') }}
                buildBody={ this.buildContainerParamsDialogBody.bind(this) }
                buildFooter={ this.buildContainerParamsDialogFooter.bind(this) }
              />
            </div>
          )}
      </div>
    );
  }
}

ChooseParamsButtons.propTypes = {
  fdrId: PropTypes.number.isRequired,

  realtimeCalibrationParams: PropTypes.object.isRequired
}

function mapStateToProps(state) {
  return {
    realtimeCalibrationParams: state.realtimeCalibrationParams
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ChooseParamsButtons);
