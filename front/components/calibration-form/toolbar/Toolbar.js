import './toolbar.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import redirect from 'actions/redirect';

class Toolbar extends Component {
    handleCreate() {
        if (typeof parseInt(this.props.fdrId) === 'number') {
            this.props.redirect('/calibration/create/fdr-id/' + this.props.fdrId);
        }
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
                      <a className='navbar-brand' href='#'>
                        <Translate value='calibration.toolbar.title' />
                      </a>
                    </div>

                    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                        <ul className='nav navbar-nav navbar-right'>
                            <li><a href='#'>
                                <span
                                    className='glyphicon glyphicon-plus' aria-hidden='true'
                                    onClick={ this.handleCreate.bind(this) }
                                >
                                </span>
                            </a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        );
    }
}

Toolbar.propTypes = {
    fdrId: PropTypes.number,
    calibrationId: PropTypes.number,

    redirect: PropTypes.func.isRequired
};

function mapStateToProps(state) {
    return {}
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Toolbar);
