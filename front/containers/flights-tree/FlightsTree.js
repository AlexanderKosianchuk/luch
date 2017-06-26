import React from 'react';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/flights-tree/toolbar/Toolbar';
import Tree from 'components/flights-tree/tree/Tree';

import showPage from 'actions/showPage';

export default function FlightsTree (props) {
    return (
        <div>
            <MainPage/>
            <Toolbar viewType='tree'/>
            <Tree/>
        </div>
    );
}
