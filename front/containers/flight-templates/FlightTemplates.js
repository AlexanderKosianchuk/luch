import React from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/flight-templates/toolbar/Toolbar';
import List from 'components/flight-templates/list/List';

class FlightTemplates extends React.Component {
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
        flightId: ownProps.match.params.flightId
    };
}

export default connect(mapStateToProps, () => { return{} })(FlightTemplates);
