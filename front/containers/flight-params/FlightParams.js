import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/flight-params/toolbar/Toolbar';
import CycloParams from 'controls/cyclo-params/CycloParams';

import showPage from 'actions/showPage';

const CYCLO_CONTEXT = 'flightParams';

class FlightParams extends React.Component {
    render () {
        return (
            <div>
                <Menu/>
                <Toolbar flightId={ this.props.flightId }/>
                <CycloParams
                    flightId={ this.props.flightId }
                    context={ CYCLO_CONTEXT }
                    chosenAnalogParams={ this.props.fdrCyclo.chosenAnalogParams }
                    chosenBinaryParams={ this.props.fdrCyclo.chosenBinaryParams }
                />
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.id,
        fdrCyclo: state.fdrCyclo
    };
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPage, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightParams);
