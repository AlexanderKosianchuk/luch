import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';
import FlightParamsOptions from 'components/flight-params-options/FlightParamsOptions';

import showPageAction from 'actions/showPage';

class FlightParams extends React.Component {
    componentDidMount() {
        this.props.showPage('flightParams', [this.props.flightId]);
    }

    render () {
        return (
            <div>
                <MainPage/>
                <FlightParamsOptions flightId={ this.props.flightId }/>
                <div id='container'></div>
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.id
    };
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPageAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightParams);
