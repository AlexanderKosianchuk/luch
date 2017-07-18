import './toolbar.sass'

import React from 'react';
import { Translate } from 'react-redux-i18n';

export default function Toolbar (props) {
    return (
        <nav className='users-table-toolbar navbar navbar-default'>
            <div className='container-fluid'>
                <div className='navbar-header'>
                  <button type='button' className='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-navbar-collapse' aria-expanded='false'>
                    <span className='sr-only'>Toggle navigation</span>
                    <span className='icon-bar'></span>
                    <span className='icon-bar'></span>
                    <span className='icon-bar'></span>
                  </button>
                  <a className='navbar-brand' href='#'><Translate value='usersTable.toolbar.usersTable' /></a>
                </div>

                <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                </div>
            </div>
        </nav>
    );
}
