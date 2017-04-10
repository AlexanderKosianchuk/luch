import './results.sass';

import React from 'react';
import { connect } from 'react-redux';

import ResultsToolbar from 'components/results-toolbar/ResultsToolbar';
import FlightFilter from 'components/flight-filter/FlightFilter';
import ResultSettlementFilter from 'components/result-settlements-filter/ResultSettlementFilter';

export default class Results extends React.Component {
    render() {
        return (<div className="results fluid-grid">
                <div className="row">
                    <div className="col-sm-14">
                        <ResultsToolbar i18n={this.props.i18n} />
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-3">
                        <FlightFilter i18n={this.props.i18n} />
                    </div>
                    <div className="col-sm-3">
                        <ResultSettlementFilter i18n={this.props.i18n} />
                    </div>
                    <div className="col-sm-6">
                        &nbsp;
                    </div>
                </div>
            </div>);
    }
}
