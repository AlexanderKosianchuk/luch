import './flight-title.sass';

import React, { Component } from 'react';
import { Translate } from 'react-redux-i18n';

export default function FlightTitle(props) {
    return (
        <div className='flights-flight-title'>
            <Translate value='flights.flightTitle.bort' />
            &nbsp;-&nbsp;
            { props.flightInfo.bort },&nbsp;
            <Translate value='flights.flightTitle.voyage' />
            &nbsp;-&nbsp;
            { props.flightInfo.voyage },&nbsp;
            <Translate value='flights.flightTitle.startCopyTime' />
            &nbsp;-&nbsp;
            { props.flightInfo.startCopyTimeFormated },&nbsp;
            <Translate value='flights.flightTitle.departureAirport' />
            &nbsp;-&nbsp;
            { props.flightInfo.departureAirport },&nbsp;
            <Translate value='flights.flightTitle.arrivalAirport' />
            &nbsp;-&nbsp;
            { props.flightInfo.arrivalAirport }
        </div>
    );
}
