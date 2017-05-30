import React from 'react';
import { connect } from 'react-redux';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/flight-templates/toolbar/Toolbar';
import List from 'components/flight-templates/list/List';

class FlightTemplates extends React.Component {
    render () {
        return (
            <div>
                <MainPage/>
                <Toolbar flightId={ this.props.flightId }/>
                <List flightId={ this.props.flightId }/>
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.id
    };
}

export default connect(mapStateToProps, () => { return{} })(FlightTemplates);
