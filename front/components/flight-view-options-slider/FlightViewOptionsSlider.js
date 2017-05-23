import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import getFlightDurationAction from 'actions/getFlightDuration';

class FlightViewOptionsSlider extends React.Component {
    render() {
        return (
            <ul className="nav navbar-nav">
                <li>1</li>
            </ul>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightDuration: bindActionCreators(getFlightDurationAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightViewOptionsSlider);
