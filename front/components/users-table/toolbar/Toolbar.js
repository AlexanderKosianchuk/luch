import './toolbar.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';
import CreateUserButton from 'components/users-table/create-user-button/CreateUserButton';

import redirect from 'actions/redirect';

class Toolbar extends Component {
  handleClick() {
    this.props.redirect('/user/create');
  }

  render() {
    return (
      <nav className='users-table-toolbar navbar navbar-default'>
        <div className='container-fluid'>
          <div className='navbar-header'>
            <NavbarToggle/>
            <a className='navbar-brand' href='#'><Translate value='usersTable.toolbar.list' /></a>
          </div>

          <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
            <CreateUserButton
            handleClick={ this.handleClick.bind(this) }
            />
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
