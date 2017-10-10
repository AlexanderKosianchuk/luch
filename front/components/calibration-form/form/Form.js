import './form.sass'

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate } from 'react-redux-i18n';
import PropTypes from 'prop-types';

import ContentLoader from 'controls/content-loader/ContentLoader';
import Param from 'components/calibration-form/param/Param';

import request from 'actions/request';
import redirect from 'actions/redirect';

class Form extends Component {
    constructor(props) {
        super(props);

        props.onSubmit((name) => this.handleSaveClick(name));
    }

    handleSaveClick(name) {
        this.calibrationName.value = name;
        let form = new FormData(this.calibrationForm);

        this.props.request(
            ['calibration', 'saveCalibration'],
            'post',
            (this.props.calibrationId === null) ? 'CREATE_CALIBRATION' : 'UPDATE_CALIBRATION',
            form
        ).then(response => this.props.redirect('/calibrations/fdr-id/' + this.props.fdrId));
    }

    buildRows(params) {
        return params.map((param, index) =>
            <Param key={ index } param={ param }/>
        );
    }

    buildForm() {
        return (
            <form
                className='calibration-form-form__container form-horizontal'
                target='_blank'
                action={ ENTRY_URL }
                ref={ (form) => { this.calibrationForm = form; }}
            >
                <div className='hidden'>
                    <input name='name' type='text' value='' ref={ (input) => { this.calibrationName = input; }} />
                    <input name='calibrationId' type='text' defaultValue={ this.props.calibrationId } />
                    <input name='fdrId' type='text' defaultValue={ this.props.fdrId } />
                </div>
                { this.buildRows(this.props.params) }
            </form>
        );
    }

    buildBody() {
        if ((this.props.pending !== false)
        ) {
            return <ContentLoader/>
        }

        return this.buildForm();
    }

    componetnWillUnmount() {
        this.props.offSubmit();
    }

    render() {
        return (
            <div className='calibration-form-form'>
                { this.buildBody() }
            </div>
        );
    }
}

Form.propTypes = {
    pending: PropTypes.bool,
    params: PropTypes.array,
    fdrId:  PropTypes.number,
    calibrationId: PropTypes.number,
    onSubmit: PropTypes.func.isRequired,
    offSubmit: PropTypes.func.isRequired
};

function mapStateToProps(state) {
    return {
        pending: state.calibration.pending,
        params: state.calibration.params || [],
        fdrId: state.calibration.fdrId,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        redirect: bindActionCreators(redirect, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Form);
