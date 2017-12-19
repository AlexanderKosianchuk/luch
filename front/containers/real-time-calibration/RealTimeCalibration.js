import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';

import VerticalToolbar from 'components/real-time-calibration/vertical-toolbar/VerticalToolbar';
import RealTimeChart from 'components/real-time-calibration/real-time-chart/RealTimeChart';

import request from 'actions/request';

class RealTimeCalibration extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div>
                <Menu />
                <VerticalToolbar/>
                <RealTimeChart/>
            </div>
        );
    }
}

function mapStateToProps() {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(RealTimeCalibration);
