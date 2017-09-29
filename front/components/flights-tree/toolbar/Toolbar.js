import './toolbar.sass'

import React from 'react';
import { Translate } from 'react-redux-i18n';

import FlightListViewSwitch from 'controls/flight-list-view-switch/FlightListViewSwitch';
import MenuDropdown from 'components/flights-tree/menu-dropdown/MenuDropdown';
import CreateFolder from 'components/flights-tree/create-folder/CreateFolder';

import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';

export default function Toolbar (props) {
    return (
        <nav className='flights-tree-toolbar navbar navbar-default'>
            <div className='container-fluid'>
                <div className='navbar-header'>
                  <NavbarToggle/>
                  <a className='navbar-brand' href='#'><Translate value='flightsTree.toolbar.flightList' /></a>
                </div>

                <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                  <FlightListViewSwitch viewType={ props.viewType } />
                  <MenuDropdown/>
                  <CreateFolder/>
                </div>
            </div>
        </nav>
    );
}
