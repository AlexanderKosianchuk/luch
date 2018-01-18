import './results.sass';

import React from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/results/toolbar/Toolbar';
import FlightFilter from 'components/results/flight-filter/FlightFilter';
import SettlementFilter from 'components/results/settlements-filter/SettlementFilter';
import SettlementsReport from 'components/results/settlements-report/SettlementsReport';

export default class Results extends React.Component {
  render() {
    return (
      <div>
        <Menu />
        <div className="results container-fluid">
          <div className="row">
            <div className="col-sm-12">
              <Toolbar/>
            </div>
          </div>
          <div className="row">
            <div className="col-sm-3">
              <FlightFilter/>
            </div>
            <div className="col-sm-3">
              <SettlementFilter/>
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
