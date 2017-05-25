import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';
import FlightListOptions from 'components/flight-list-options/FlightListOptions';

import showPage from 'actions/showPage';

class Flights extends React.Component {
    componentDidMount()
    {
        this.callShowPage();
    }

    componentDidUpdate(prevProps, prevState)
    {
        if (prevProps.viewType !== this.props.viewType) {
            this.callShowPage();
        }
    }

    callShowPage()
    {
        if ((this.props.viewType === 'table')) {
            this.props.showPage('flightsTableShow');
        } else {
            this.props.showPage('flightsTreeShow');
        }
    }

    render () {
        return (
            <div>
                <MainPage/>
                <FlightListOptions viewType={ this.props.viewType }/>
                <div id='container'></div>
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        viewType: ownProps.match.params.viewType
    };
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPage, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Flights);
