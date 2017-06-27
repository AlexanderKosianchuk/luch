import './flight-title.sass';

import React, { Component } from 'react';
import { Translate } from 'react-redux-i18n';

export default function FlightTitle(props) {
    if (!props.flightInfo
        || !props.flightInfo.bort
        || !props.flightInfo.voyage
        || !props.flightInfo.departureAirport
        || !props.flightInfo.arrivalAirport
        || !props.flightInfo.startCopyTimeFormated
    ) {
        return null
    }

    return (
        <div className='flights-tree-flight-title' data-flight-id={ props.flightInfo.id }>
            <Translate value='flightsTree.flightTitle.bort' />
            &nbsp;-&nbsp;
            { props.flightInfo.bort },&nbsp;
            <Translate value='flightsTree.flightTitle.voyage' />
            &nbsp;-&nbsp;
            { props.flightInfo.voyage },&nbsp;
            <Translate value='flightsTree.flightTitle.startCopyTime' />
            &nbsp;-&nbsp;
            { props.flightInfo.startCopyTimeFormated },&nbsp;
            <Translate value='flightsTree.flightTitle.departureAirport' />
            &nbsp;-&nbsp;
            { props.flightInfo.departureAirport },&nbsp;
            <Translate value='flightsTree.flightTitle.arrivalAirport' />
            &nbsp;-&nbsp;
            { props.flightInfo.arrivalAirport }
        </div>
    );
}
