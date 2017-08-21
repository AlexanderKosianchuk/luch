import './toolbar.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import redirect from 'actions/redirect';

class Toolbar extends Component {
    handleClick() {
        this.props.redirect('/users');
    }

    render() {
        return (
            <nav className='user-activity-toolbar navbar navbar-default'>
                <div className='container-fluid'>
                    <div className='navbar-header'>
                      <button type='button' className='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-navbar-collapse' aria-expanded='false'>
                        <span className='sr-only'>Toggle navigation</span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                      </button>
                      <a className='navbar-brand' href='#'><Translate value='userActivity.toolbar.list' /></a>
                    </div>

                    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                        <ul className='user-activity-toolbar__list nav navbar-nav navbar-right'>
                            <li><a href='#' className='user-activity-toolbar__a'
                                    onClick={ this.handleClick.bind(this) }>
                                <span
                                    className='glyphicon glyphicon-list' aria-hidden='true'>
                                </span>
                            </a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        );
    }
}

function mapStateToProps() {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Toolbar);
