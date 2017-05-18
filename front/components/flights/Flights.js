import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';

import showPageAction from 'actions/showPage';

class Flights extends React.Component {
    componentDidMount() {
        this.props.showPage('flightListShow');
    }

    render () {
        return (
            <div>
                <MainPage />
                <div id='flightsContainer'></div>
            </div>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPageAction, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(Flights);
