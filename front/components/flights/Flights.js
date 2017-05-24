import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';
import FlightListOptions from 'components/flight-list-options/FlightListOptions';

import showPage from 'actions/showPage';

class Flights extends React.Component {
    componentDidMount() {
        this.props.showPage('flightListShow');
    }

    render () {
        return (
            <div>
                <MainPage/>
                <FlightListOptions/>
                <div id='container'></div>
            </div>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPage, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(Flights);
