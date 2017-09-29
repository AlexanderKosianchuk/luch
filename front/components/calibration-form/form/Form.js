import './form.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';
import PropTypes from 'prop-types';

import ContentLoader from 'controls/content-loader/ContentLoader';
import Param from 'components/calibration-form/param/Param';

class Form extends Component {
    constructor(props) {
        super(props);

        this.calibrationForm = null;
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
                ref={ (form) => { this.calibrationForm = form; }}
            >
                <div className='hidden'>
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
    params: PropTypes.array
};

function mapStateToProps(state) {
    return {
        pending: state.calibration.pending,
        params: state.calibration.params || [],
    };
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Form);
