import './toolbar.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';

import FlightViewOptionsSwitch from 'controls/flight-view-options-switch/FlightViewOptionsSwitch';
import FormPrint from 'components/flight-events/form-print/FormPrint';

export default class Toolbar extends Component {
  render() {
    return (
      <nav className="flight-events-toolbar navbar navbar-default">
        <div className="container-fluid">
          <div className="navbar-header">
            <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
            <span className="sr-only">Toggle navigation</span>
            <span className="icon-bar"></span>
            <span className="icon-bar"></span>
            <span className="icon-bar"></span>
            </button>
          </div>

          <div className="collapse navbar-collapse" id="bs-navbar-collapse">
            <FlightViewOptionsSwitch
              view={ 'events' }
              flightId={ this.props.flightId }
            />
            <FormPrint
              flightId={ this.props.flightId }
            />
          </div>
        </div>
      </nav>
    );
  }
}

Toolbar.propTypes = {
  flightId: PropTypes.number.isRequired
};
