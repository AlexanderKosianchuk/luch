import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/chart/toolbar/Toolbar';

import request from 'actions/request';
import showPage from 'actions/showPage';

class Chart extends Component {
  componentDidMount() {
    Promise.all([
      this.props.request(
        ['flightTemplate', 'get'],
        'get',
        'FLIGHT_TEMPLATE',
        {
          flightId: this.props.flightId,
          templateId: this.props.templateId
        }
      ),
      this.props.request(
        ['flights', 'getFlightInfo'],
        'get',
        'FLIGHT',
        { flightId: this.props.flightId }
      )
    ]).then(() => {
      let params = this.props.params || [];

      let analogParamsCodes = params
        .filter((item) => (item.type === 'ap'))
        .map((item) => item.code);

      let binaryParamsCodes = params
        .filter((item) => (item.type === 'bp'))
        .map((item) => item.code);

      this.props.showPage('chartShow', [
        this.props.flightId,
        this.props.templateId,
        this.props.stepLength,
        this.props.startFlightTime,
        this.props.fromFrame,
        this.props.toFrame,
        analogParamsCodes,
        binaryParamsCodes
      ]);
    });
  }

  render () {
    return (
      <div>
        <Menu/>
        <Toolbar flightId={ this.props.flightId } />
        <div id='container'></div>
      </div>
    );
  }
}

function mapStateToProps(state, ownProps) {
  return {
    flightId: ownProps.match.params.flightId,
    templateId: ownProps.match.params.templateId,
    fromFrame: ownProps.match.params.fromFrame,
    toFrame: ownProps.match.params.toFrame,
    params: state.flightTemplate.params,
    stepLength: state.flight.stepLength,
    startFlightTime: state.flight.startFlightTime,
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    showPage: bindActionCreators(showPage, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Chart);
