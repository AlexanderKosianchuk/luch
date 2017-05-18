import './results-toolbar.sass';

import React from 'react';
import { Translate } from 'react-redux-i18n';

export default class ResultsToolbar extends React.Component {
    render() {
        return (
            <div className="results-toolbar">
                <h4><Translate value='resultsToolbar.aggregatedStatistics'/></h4>
            </div>
        );
    }
}
