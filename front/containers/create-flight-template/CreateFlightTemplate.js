import React from 'react';
import { connect } from 'react-redux';

import MainPage from 'controls/main-page/MainPage';
//import FlightTemplatesOptions from 'components/flight-templates-options/FlightTemplatesOptions';
//import FlightTemplatesList from 'components/flight-templates-list/FlightTemplatesList';

class CreateFlightTemplate extends React.Component {
    render () {
        return (
            <div>
                <MainPage/>
                /*<Options flightId={ this.props.flightId }/>*/
                /*<List flightId={ this.props.flightId }/>*/
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.id
    };
}

export default connect(mapStateToProps, () => { return{} })(CreateFlightTemplate);
