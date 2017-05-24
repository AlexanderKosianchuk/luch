import React from 'react';
import { connect } from 'react-redux';

import MainPage from 'components/main-page/MainPage';
import FlightTemplatesOptions from 'components/flight-templates-options/FlightTemplatesOptions';
import FlightTemplatesList from 'components/flight-templates-list/FlightTemplatesList';

class FlightTemplates extends React.Component {
    render () {
        return (
            <div>
                <MainPage/>
                <FlightTemplatesOptions flightId={ this.props.flightId }/>
                <FlightTemplatesList flightId={ this.props.flightId }/>
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
