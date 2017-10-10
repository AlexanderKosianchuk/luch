import './toolbar.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';
import ControlButtons from 'components/user-form/control-buttons/ControlButtons';

import redirect from 'actions/redirect';

class Toolbar extends Component {
    handleListClick() {
        this.props.redirect('/users');
    }

    render() {
        return (
            <nav className='navbar navbar-default'>
                <div className='container-fluid'>
                    <div className='navbar-header'>
                      <NavbarToggle/>
                      <a className='navbar-brand' href='#'>
                        { I18n.t('userForm.toolbar.' + this.props.type) }
                      </a>
                    </div>

                    <ControlButtons
                        handleSaveClick={ this.props.submit }
                        handleListClick={ this.handleListClick.bind(this) }
                    />
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
