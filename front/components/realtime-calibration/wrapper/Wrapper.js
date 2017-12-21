import React, { Component } from 'react';

import VerticalToolbar from 'components/realtime-calibration/vertical-toolbar/VerticalToolbar';
import RealTimeChart from 'components/realtime-calibration/realtime-chart/RealTimeChart';

export default function Wrapper () {
    return (
        <div className='row'>
            <div className='col-sm-4'>
                <VerticalToolbar/>
            </div>
            <div className='col-sm-8'>
                <RealTimeChart/>
            </div>
        </div>
    );
}
