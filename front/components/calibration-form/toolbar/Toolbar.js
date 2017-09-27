import './toolbar.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';
import ToolbarInput from 'controls/toolbar-input/ToolbarInput';

import redirect from 'actions/redirect';

class Toolbar extends Component {
    handleSaveClick() {
        this.props.redirect('/calibration/create/fdr-id/' + this.props.fdrId);
    }

    render() {
        return (
            <nav className='calibrations-toolbar navbar navbar-default'>
                <div className='container-fluid'>
                    <div className='navbar-header'>
                      <button type='button' className='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-navbar-collapse' aria-expanded='false'>
                        <span className='sr-only'>Toggle navigation</span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                      </button>
                      <span className='navbar-brand' href='#'>
                        <Translate value='calibrationForm.toolbar.title' />
                      </span>
                      <span className='navbar-brand' href='#'>
                        <Translate value='calibrationForm.toolbar.title' />
                      </span>
                      <a className='navbar-brand' href='#'>
                        <Translate value='calibrationForm.toolbar.title' />
                      </a>
                    </div>

                    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                        <ToolbarInput
                            handleSaveClick={ this.handleSaveClick.bind(this) }
                        />
                    </div>
                </div>
            </nav>
        );
    }
}

Toolbar.propTypes = {
    fdrId: PropTypes.number,
    calibrationId: PropTypes.number,

    calibrationName: PropTypes.string,
    fdrName: PropTypes.string,

    redirect: PropTypes.func.isRequired
};

function mapStateToProps(state) {
    return {
        calibrationName: state.calibration.name,
        fdrName: state.calibration.fdrName
    }
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Toolbar);
