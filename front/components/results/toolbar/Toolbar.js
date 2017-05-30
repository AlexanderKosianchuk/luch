import './toolbar.sass';

import React from 'react';
import { Translate } from 'react-redux-i18n';

export default class Toolbar extends React.Component {
    render() {
        return (
            <div className="flights-toolbar">
                <h4><Translate value='results.toolbar.aggregatedStatistics'/></h4>
            </div>
        );
    }
}
