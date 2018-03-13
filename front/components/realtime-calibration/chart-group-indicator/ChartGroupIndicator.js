import './chart-group-indicator.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import transmit from 'actions/transmit';

class ChartGroupIndicator extends Component {
  constructor(props) {
    super(props);

    this.state = {
      group: '-'
    }
  }
  handleClick(indicator) {
    if ((this.state.group === '-')
      && (indicator < 0)
    ) {
      return;
    }

    if ((this.state.group === '-')
      && (indicator > 0)
    ) {
      this.setState({group: 1});
      return;
    }

    if ((this.state.group === 1)
      && (indicator < 0)
    ) {
      this.setState({group: '-'});
      return;
    }

    this.setState({group: this.state.group + indicator});
  }

  handleChartClick() {
    if (this.state.group === '-') {
      this.props.transmit('CHANGE_REALTIME_CALIBRATION_CHART_PARAM_CHECKSTATE', {
        ...this.props.param,
        ...{ state: true }
      });
    } else {
      this.props.transmit('ADD_REALTIME_CALIBRATION_CHART_PARAM_TO_GROUP', {
        ...this.props.param,
        ...{ group: this.state.group }
      });
    }
  }

  render() {
    return (
      <div className='realtime-calibration-chart-group-indicator'>
        <div className='realtime-calibration-chart-group-indicator__box'>
          <div className='realtime-calibration-chart-group-indicator__chevron'
            onClick={ this.handleClick.bind(this, 1) }
          >
            <span className='glyphicon glyphicon-chevron-up'></span>
          </div>
          <div className='realtime-calibration-chart-group-indicator__text-box'>
            <div className='realtime-calibration-chart-group-indicator__text'
              onClick={ this.handleChartClick.bind(this) }
            >
              { this.state.group }
            </div>
          </div>
          <div className='realtime-calibration-chart-group-indicator__chevron'
            onClick={ this.handleClick.bind(this, -1) }
          >
            <span className='glyphicon glyphicon-chevron-down'></span>
          </div>
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {};
}

function mapDispatchToProps(dispatch) {
  return {
    transmit: bindActionCreators(transmit, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ChartGroupIndicator);
