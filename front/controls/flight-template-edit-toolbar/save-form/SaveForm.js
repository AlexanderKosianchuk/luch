import './save-form.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';
import _isEmpty from 'lodash.isempty';

import redirect from 'actions/redirect';
import setTemplate from 'actions/setTemplate';

class SaveForm extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            inputValue: props.templateName || ''
        }
    }

    saveTemplate() {
        Promise.resolve(this.props.setTemplate({
            flightId: this.props.flightId,
            templateName: this.state.inputValue,
            analogParams: this.props.fdrCyclo.chosenAnalogParams,
            binaryParams: this.props.fdrCyclo.chosenBinaryParams
        })).then(() => {
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

    render() {
        return (
            <form className='flight-template-edit-save-form form-inline'>
                <input className='form-control flight-template-edit-save-form__input'
                    type='text'
                    placeholder={ I18n.t('flightTemplateEdit.saveForm.templateName') }
                    value={ this.state.inputValue }
                    onChange={ this.handleChange.bind(this) }
                />
                <span className='flight-template-edit-save-form__button-container'>
                    { this.buildButton() }
                </span>
            </form>
        );
    }
}

function mapStateToProps (state) {
    return {
        fdrCyclo: state.fdrCyclo
    }
}

function mapDispatchToProps(dispatch) {
    return {
        setTemplate: bindActionCreators(setTemplate, dispatch),
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(SaveForm);
