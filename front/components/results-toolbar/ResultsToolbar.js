import './results-toolbar.sass';

import React from 'react';

export default class ResultsToolbar extends React.Component {
    render() {
        return <div className="results-toolbar">
            {this.props.i18n.options}
        </div>;
    }
}
