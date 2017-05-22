import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';
import FlightTemplatesOptions from 'components/flight-templates-options/FlightTemplatesOptions';

import showPageAction from 'actions/showPage';

class FlightTemplates extends React.Component {
    componentDidMount() {
        this.props.showPage('flightTemplates', [this.props.flightId]);
    }

    render () {
        return (
            <div>
                <MainPage/>
                <FlightTemplatesOptions flightId={ this.props.flightId }/>
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

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplates);
