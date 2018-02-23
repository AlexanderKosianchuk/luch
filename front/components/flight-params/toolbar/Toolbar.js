import './toolbar.sass'

import React from 'react';

import FlightViewOptionsSwitch from 'controls/flight-view-options-switch/FlightViewOptionsSwitch';
import FlightRangeSlider from 'controls/flight-range-slider/FlightRangeSlider';
import ShowChartButton from 'components/flight-params/show-chart-button/ShowChartButton';

import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';

export default class Toolbar extends React.Component {
  render() {
    return (
      <nav className='flight-params-toolbar navbar navbar-default'>
        <div className='container-fluid'>
          <div className='navbar-header'>
            <NavbarToggle/>
          </div>

          <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
            <FlightViewOptionsSwitch
              view={ 'params' }
              flightId={ this.props.flightId }
            />
            <FlightRangeSlider
              flightId={ this.props.flightId }
            />
            <ShowChartButton
              flightId={ this.props.flightId }
            />
          </div>
        </div>
      </nav>
    );
  }
}
