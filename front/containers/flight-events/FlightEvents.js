import React from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/flight-events/toolbar/Toolbar';
import List from 'components/flight-events/list/List';


class FlightEvents extends React.Component {
    render () {
        return (
            <div>
                <Menu/>
                <Toolbar flightId={ this.props.flightId }/>
                <List flightId={ this.props.flightId }/>
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: parseInt(ownProps.match.params.flightId)
    };
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightEvents);
