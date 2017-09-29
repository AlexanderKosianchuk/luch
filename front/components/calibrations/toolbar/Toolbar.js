import './toolbar.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';
import FdrSelector from 'controls/fdr-selector/FdrSelector';

import redirect from 'actions/redirect';

class Toolbar extends Component {
    handleChange(chosenFdr) {
        let parsedUrl = new URL(window.location.href);
        let url = parsedUrl.pathname;

        if (url.indexOf('fdr-id') !== -1) {
            url = url.replace(/fdr-id\/[0-9]+/, 'fdr-id/' + chosenFdr.id)
        } else {
            url = url.replace(/calibrations/, 'calibrations/fdr-id/' + chosenFdr.id)
        }

        this.props.redirect(url);
    }

    handleCreate() {
        if (typeof parseInt(this.props.fdrId) === 'number') {
            this.props.redirect('/calibration/create/fdr-id/' + this.props.fdrId);
        }
    }

    render() {
        let isClear = true;
        if (this.props.fdrId) {
            isClear = false;
        }

        return (
            <nav className='calibrations-toolbar navbar navbar-default'>
                <div className='container-fluid'>
                    <div className='navbar-header'>
                      <NavbarToggle/>
                      
                      <a className='navbar-brand' href='#'>
                        <Translate value='calibration.toolbar.title' />
                      </a>
                    </div>

                    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                        <ul className='nav navbar-nav navbar-right'>
                            <FdrSelector
                                chosenFdrId={ this.props.fdrId }
                                isClear={ isClear }
                                handleChange={ this.handleChange.bind(this) }
                            />
                            <li><a href='#' className={ isClear ? 'calibrations-toolbar__is-hidden' : ''}>
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
    page:  PropTypes.number,
    pageSize: PropTypes.number,

    chosen: PropTypes.object.isRequired,
    redirect: PropTypes.func.isRequired
};

function mapStateToProps(state) {
    return {
        chosen: state.fdrs.chosen
    }
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Toolbar);
