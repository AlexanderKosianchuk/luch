import './results-toolbar.sass';

import React from 'react';

export default class ResultsToolbar extends React.Component {
    render() {
        return (
            <div className="results-toolbar">
                <h4>{this.props.i18n.aggregatedStatistics}</h4>
            </div>
        );
    }
}
