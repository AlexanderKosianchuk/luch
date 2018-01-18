import './toolbar.sass'

import React from 'react';
import { Translate } from 'react-redux-i18n';

import FlightListViewSwitch from 'controls/flight-list-view-switch/FlightListViewSwitch';
import MenuDropdown from 'components/flights-table/menu-dropdown/MenuDropdown';

import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';

export default function Toolbar (props) {
  return (
    <nav className='flights-table-toolbar navbar navbar-default'>
      <div className='container-fluid'>
        <div className='navbar-header'>
          <NavbarToggle/>
          <a className='navbar-brand' href='#'><Translate value='flightsTable.toolbar.flightList' /></a>
        </div>

        <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
          <FlightListViewSwitch viewType={ props.viewType } />
          <MenuDropdown/>
        </div>
      </div>
    </nav>
  );
}
