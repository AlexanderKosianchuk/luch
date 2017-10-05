import './toolbar.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';

import PropTypes from 'prop-types';
import { Translate, I18n } from 'react-redux-i18n';
import ToolbarInput from 'controls/toolbar-input/ToolbarInput';
import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';

class Toolbar extends Component {
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
                            handleSaveClick={ this.props.submit }
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

    submit: PropTypes.func.isRequired
};

function mapStateToProps(state) {
    return {
        calibrationName: state.calibration.name,
        fdrName: state.calibration.fdrName
    }
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Toolbar);
