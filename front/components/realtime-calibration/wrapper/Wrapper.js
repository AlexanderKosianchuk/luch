import React, { Component } from 'react';

import VerticalToolbar from 'components/realtime-calibration/vertical-toolbar/VerticalToolbar';
import ChartContainer from 'components/realtime-calibration/chart-container/ChartContainer';

const INTERACTION_URL = 'http://localhost:1337';

export default function Wrapper () {
    return (
        <div className='row'>
            <div className='col-sm-4'>
                <VerticalToolbar
                    interactionUrl={ INTERACTION_URL }
                />
            </div>
            <div className='col-sm-8'>
                <ChartContainer
                    interactionUrl={ INTERACTION_URL }
                />
            </div>
        </div>
    );
}
