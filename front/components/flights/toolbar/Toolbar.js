import './toolbar.sass'

import React from 'react';
import { Translate } from 'react-redux-i18n';

import TypeSwitch from 'components/flights/type-switch/TypeSwitch';
import MenuDropdown from 'components/flights/menu-dropdown/MenuDropdown';
import CreateFolder from 'components/flights/create-folder/CreateFolder';

export default function Toolbar (props) {
    return (
        <nav className='flights-toolbar navbar navbar-default'>
            <div className='container-fluid'>
                <div className='navbar-header'>
                  <button type='button' className='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-navbar-collapse' aria-expanded='false'>
                    <span className='sr-only'>Toggle navigation</span>
                    <span className='icon-bar'></span>
                    <span className='icon-bar'></span>
                    <span className='icon-bar'></span>
                  </button>
                  <a className='navbar-brand' href='#'><Translate value='flights.toolbar.flightList' /></a>
                </div>

                <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                  <TypeSwitch viewType={ props.viewType } />
                  <MenuDropdown/>
                  { props.viewType === 'tree' ? <CreateFolder/> : '' }
                </div>
            </div>
        </nav>
    );
}
