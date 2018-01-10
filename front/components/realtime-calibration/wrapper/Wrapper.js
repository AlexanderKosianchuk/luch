import React, { Component } from 'react';

import VerticalToolbar from 'components/realtime-calibration/vertical-toolbar/VerticalToolbar';
import ChartContainer from 'components/realtime-calibration/chart-container/ChartContainer';
import uuidV4 from 'uuid/v4';

const INTERACTION_URL = 'http://localhost:1337';
const UID = uuidV4().substring(0, 18).replace(/-/g, '');

export default function Wrapper () {

    return (
        <div className='row'>
            <div className='col-sm-3'>
                <VerticalToolbar
                    interactionUrl={ INTERACTION_URL }
                    uid={ UID }
                />
            </div>
            <div className='col-sm-9'>
                <ChartContainer
                    interactionUrl={ INTERACTION_URL }
                    uid={ UID }
                />
            </div>
        </div>
    );
}
