import React from 'react';
import { connect } from 'react-redux';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/create-flight-template/toolbar/Toolbar';
import CycloParams from 'controls/cyclo-params/CycloParams';

class CreateFlightTemplate extends React.Component {
    render () {
        return (
            <div>
                <MainPage/>
                <Toolbar flightId={ this.props.flightId }/>
                <CycloParams
                    flightId={ this.props.flightId }
                    colorPickerEnabled={ false }
                />
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.flightId,
        fdrId: ownProps.match.params.fdrId
    };
}

export default connect(mapStateToProps, () => { return{} })(CreateFlightTemplate);
