import './wrapper.sass';

import React, { Component } from 'react';

import VerticalToolbar from 'components/real-time-calibration/vertical-toolbar/VerticalToolbar';
import RealTimeChart from 'components/real-time-calibration/real-time-chart/RealTimeChart';

export default function Wrapper () {
    return (
        <div className='real-time-calibration-wrapper'>
            <VerticalToolbar/>
            <RealTimeChart/>
        </div>
    );
}
