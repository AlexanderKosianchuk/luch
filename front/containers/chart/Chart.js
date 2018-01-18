import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/chart/toolbar/Toolbar';

import request from 'actions/request';
import showPage from 'actions/showPage';

class Chart extends React.Component {
  componentDidMount() {
    Promise.all([
      this.props.request(
        ['templates', 'getTemplate'],
        'get',
        'TEMPLATE',
        {
          flightId: this.props.flightId,
          templateName: this.props.templateName
        }
      ),
      this.props.request(
        ['flights', 'getFlightInfo'],
        'get',
        'FLIGHT',
        { flightId: this.props.flightId }
      )
    ]).then(() => {
      let analogParams = this.props.templateAnalogParams || [];
      let binaryParams = this.props.templateBinaryParams || [];

      let analogParamsCodes = [];
      let binaryParamsCodes = [];

      analogParams.forEach((item) => {
        analogParamsCodes.push(item.code);
      });

      binaryParams.forEach((item) => {
        binaryParamsCodes.push(item.code);
      });

      this.props.showPage('chartShow', [
        this.props.flightId,
        this.props.templateName,
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
    templateName: ownProps.match.params.templateName,
    fromFrame: ownProps.match.params.fromFrame,
    toFrame: ownProps.match.params.toFrame,
    templateAnalogParams: state.template.ap,
    templateBinaryParams: state.template.bp,
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
