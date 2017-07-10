import React from 'react';
import ReactResizeDetector from 'react-resize-detector';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/flights-tree/toolbar/Toolbar';
import View from 'components/flights-tree/view/View';

export default function FlightsTree () {
    return (
        <div>
            <MainPage/>
            <Toolbar viewType='tree'/>
            <View/>
        </div>
    );
}
