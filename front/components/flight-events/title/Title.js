import './title.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

const titleStructure = [
  { label: 'fdrName', key: 'fdrName'},
  { label: 'bort', key: 'bort'},
  { label: 'voyage', key: 'voyage'},
  { label: 'startCopyTime', key: 'startCopyTimeFormated'},
  { label: 'departureAirport', key: 'departureAirport'},
  { label: 'arrivalAirport', key: 'arrivalAirport'}
];

export default class Title extends Component {
  buildMainTitle() {
    return titleStructure.map((item) => {
      return (
        <span key={ item.key }>
          <Translate value={ 'flightEvents.title.' + item.label} />
          &nbsp;-&nbsp;
          { this.props.flight[item.key] },&nbsp;
        </span>
      );
    });
  }

  buildAditionalInfo() {
    let aditionalInfo = this.props.flight.aditionalInfo || {};
    if (!Array.isArray(Object.keys(aditionalInfo))) {
      return '';
    }

    return Object.keys(aditionalInfo).map((key) => {
      return (
        <span key={ key }>
          <Translate value={ 'flightEvents.title.' + key} />
          &nbsp;-&nbsp;
          { aditionalInfo[key] },&nbsp;
        </span>
      );
    });
  }

  render() {
    return (
      <div className='flight-events-title'>
        { this.buildMainTitle() }
        { this.buildAditionalInfo() }
      </div>
    );
  }
}

Title.propTypes = {
  flight: PropTypes.shape({
    fdrName: PropTypes.string,
    bort: PropTypes.string,
    voyage: PropTypes.string,
    departureAirport: PropTypes.string,
    arrivalAirport: PropTypes.string,
    startCopyTimeFormated: PropTypes.string,
    fdrName: PropTypes.string,
    aditionalInfo: PropTypes.object
  }).isRequired
};
