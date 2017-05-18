import './results.sass';

import React from 'react';
import { connect } from 'react-redux';

import MainPage from 'components/main-page/MainPage';
import ResultsToolbar from 'components/results-toolbar/ResultsToolbar';
import FlightFilter from 'components/flight-filter/FlightFilter';
import ResultSettlementFilter from 'components/result-settlements-filter/ResultSettlementFilter';
import SettlementsReport from 'components/settlements-report/SettlementsReport';

export default class Results extends React.Component {
    render() {
        return (
            <div>
                <MainPage />
                <div className="results container-fluid">
                    <div className="row">
                        <div className="col-sm-12">
                            <ResultsToolbar/>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-sm-3">
                            <FlightFilter/>
                        </div>
                        <div className="col-sm-3">
                            <ResultSettlementFilter/>
                        </div>
                        <div className="col-sm-6">
                            <SettlementsReport/>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}
