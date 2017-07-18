import './flight-title.sass';

import React, { Component } from 'react';
import { Translate } from 'react-redux-i18n';

export default function FlightTitle(props) {
    if (!props.flight
        || !props.flight.bort
        || !props.flight.voyage
        || !props.flight.departureAirport
        || !props.flight.arrivalAirport
        || !props.flight.startCopyTimeFormated
    ) {
        return null
    }

    return (
        <div className='flights-tree-flight-title' data-flight-id={ props.flight.id }>
            <Translate value='flightsTree.flightTitle.bort' />
            &nbsp;-&nbsp;
            { props.flight.bort },&nbsp;
            <Translate value='flightsTree.flightTitle.voyage' />
            &nbsp;-&nbsp;
            { props.flight.voyage },&nbsp;
            <Translate value='flightsTree.flightTitle.startCopyTime' />
            &nbsp;-&nbsp;
            { props.flight.startCopyTimeFormated },&nbsp;
            <Translate value='flightsTree.flightTitle.departureAirport' />
            &nbsp;-&nbsp;
            { props.flight.departureAirport },&nbsp;
            <Translate value='flightsTree.flightTitle.arrivalAirport' />
            &nbsp;-&nbsp;
            { props.flight.arrivalAirport }
        </div>
    );
}
