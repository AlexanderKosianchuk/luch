import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';

import redirect from 'actions/redirect';
import request from 'actions/request';

class ShowChartButton extends React.Component {
  buildButton() {
    if (_isEmpty(this.props.fdrCyclo.chosenAnalogParams)) {
      return '';
    }

    return <span
      className='glyphicon glyphicon-picture'
      aria-hidden='true'>
    </span>;
  }

  showChart() {
    let templateName = 'last';
    this.props.request(
      ['flightTemplate', 'set'],
      'post',
      'FLIGHT_TEMPLATE',
      {
        flightId: this.props.flightId,
        templateName: templateName,
        analogParams: this.props.fdrCyclo.chosenAnalogParams,
        binaryParams: this.props.fdrCyclo.chosenBinaryParams
      }
    ).then((response) => {
      this.props.redirect('/chart/'
        + 'flight-id/'+ this.props.flightId + '/'
        + 'template-id/'+ response.id + '/'
        + 'from-frame/'+ this.props.startFrame + '/'
        + 'to-frame/'+ this.props.endFrame
      );
    });
  }

  render() {
    return <ul className='nav navbar-nav navbar-right'>
      <li><a href='#' onClick={ this.showChart.bind(this) }>
        { this.buildButton() }
      </a></li>
    </ul>;
  }
}

function mapStateToProps(state) {
  return {
    fdrCyclo: state.fdrCyclo,
    startFrame: state.flight.selectedStartFrame,
    endFrame: state.flight.selectedEndFrame
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    redirect: bindActionCreators(redirect, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ShowChartButton);
