import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import request from 'actions/request';

class RealTimeChart extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div>1</div>
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

export default connect(mapStateToProps, mapDispatchToProps)(RealTimeChart);
