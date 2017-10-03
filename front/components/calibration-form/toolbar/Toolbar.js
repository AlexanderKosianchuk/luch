import './toolbar.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';
import ToolbarInput from 'controls/toolbar-input/ToolbarInput';
import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';

import redirect from 'actions/redirect';

class Toolbar extends Component {
    handleSaveClick() {
        //this.props.redirect('/calibration/create/fdr-id/' + this.props.fdrId);
    }

    render() {
        return (
            <nav className='calibrations-toolbar navbar navbar-default'>
                <div className='container-fluid'>
                    <div className='navbar-header'>
                      <NavbarToggle/>
                      <span className='navbar-brand' href='#'>
                        <Translate value='calibrationForm.toolbar.title'
                            fdrName={ this.props.fdrName || '' }
                        />
                      </span>

                    </div>

                    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                        <ToolbarInput
                            handleSaveClick={ this.handleSaveClick.bind(this) }
                            value={ this.props.calibrationName || '' }
                        />
                    </div>
                </div>
            </nav>
        );
    }
}

Toolbar.propTypes = {
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
