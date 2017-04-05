import React from 'react';

import FlightListTypeSwitch from 'components/flight-list-type-switch/FlightListTypeSwitch';
import FlightListMenuDropdown from 'components/flight-list-menu-dropdown/FlightListMenuDropdown';

export default function FlightListOptions (props) {
    return (
        <nav className="navbar navbar-default">
            <div className="container-fluid">
                <div className="navbar-header">
                  <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span className="sr-only">Toggle navigation</span>
                    <span className="icon-bar"></span>
                    <span className="icon-bar"></span>
                    <span className="icon-bar"></span>
                  </button>
                  <a className="navbar-brand" href="#">{ props.i18n.flightList }</a>
                </div>

                <div className="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                  <FlightListTypeSwitch
                    i18n={ props.i18n }
                    flightViewService= { props.flightViewService }
                  />

                  <FlightListMenuDropdown
                    i18n={ props.i18n }
                    flightMenuService= { props.flightMenuService }
                  />

                </div>
            </div>
        </nav>
    );
}
