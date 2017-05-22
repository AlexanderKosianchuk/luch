import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';
import FlightEventsOptions from 'components/flight-events-options/FlightEventsOptions';

import showPageAction from 'actions/showPage';

class FlightEvents extends React.Component {
    componentDidMount() {
        this.props.showPage('flightEvents', [this.props.flightId]);
    }

    render () {
        return (
            <div>
                <MainPage/>
                <FlightEventsOptions flightId={ this.props.flightId }/>
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

export default connect(mapStateToProps, mapDispatchToProps)(FlightEvents);
