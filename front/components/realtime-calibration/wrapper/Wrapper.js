import React, { Component } from 'react';

import VerticalToolbar from 'components/realtime-calibration/vertical-toolbar/VerticalToolbar';
import ChartContainer from 'components/realtime-calibration/chart-container/ChartContainer';

export default function Wrapper () {
    return (
        <div className='row'>
            <div className='col-sm-4'>
                <VerticalToolbar/>
            </div>
            <div className='col-sm-8'>
                <ChartContainer/>
            </div>
        </div>
    );
}
