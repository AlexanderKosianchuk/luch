import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/flight-events/toolbar/Toolbar';

import showPage from 'actions/showPage';

class FlightEvents extends React.Component {
    componentDidMount() {
        this.props.showPage('flightEvents', [this.props.flightId]);
    }

    render () {
        return (
            <div>
                <MainPage/>
                <Toolbar flightId={ this.props.flightId }/>
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
        showPage: bindActionCreators(showPage, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightEvents);
