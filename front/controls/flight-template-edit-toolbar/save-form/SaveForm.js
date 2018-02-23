import './save-form.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';
import _isEmpty from 'lodash.isempty';

import redirect from 'actions/redirect';
import request from 'actions/request';

class SaveForm extends Component {
  constructor(props) {
    super(props);

    this.state = {
      inputValue: props.templateName || ''
    }
  }

  saveTemplate() {
    this.props.request(
      ['flightTemplate', 'set'],
      'post',
      'FLIGHT_TEMPLATE',
      {
        flightId: this.props.flightId,
        templateId: this.props.templateId,
        templateName: this.state.inputValue,
        analogParams: this.props.fdrCyclo.chosenAnalogParams,
        binaryParams: this.props.fdrCyclo.chosenBinaryParams
      }
    ).then(() => {
      this.props.redirect('/flight-templates/' + this.props.flightId);
    });
  }

  buildButton() {
    if (_isEmpty(this.props.fdrCyclo.chosenAnalogParams)
      || this.state.inputValue.length < 4
    ) {
      return '';
    }

    return (
      <span
        className='flight-template-edit-save-form__button glyphicon glyphicon-floppy-disk'
        aria-hidden='true'
        onClick={ this.saveTemplate.bind(this) }
      >
      </span>
    );
  }

  handleChange(event) {
    this.setState({
      inputValue: event.target.value
    });
  }

  isDisabled() {
    return this.props.servisePurpose && this.props.servisePurpose.isDefault;
  }

  render() {
    return (
      <form className='flight-template-edit-save-form form-inline'>
        <input className='form-control flight-template-edit-save-form__input'
          type='text'
          placeholder={ I18n.t('flightTemplateEdit.saveForm.templateName') }
          value={ this.state.inputValue }
          disabled={ this.isDisabled()   }
          onChange={ this.handleChange.bind(this) }
        />
        <span className='flight-template-edit-save-form__button-container'>
          { this.buildButton() }
        </span>
      </form>
    );
  }
}

function mapStateToProps(state) {
  return {
    fdrCyclo: state.fdrCyclo
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    redirect: bindActionCreators(redirect, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(SaveForm);
