import './view.sass';

import React from 'react';

import Tree from 'components/flights-tree/tree/Tree';
import ShortEvents from 'components/flights-tree/short-events/ShortEvents';

export default function View() {
    return (
        <div className='flights-tree-view'>
            <Tree/>
            <ShortEvents/>
        </div>
    );
}
