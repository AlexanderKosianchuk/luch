import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/flights/toolbar/Toolbar';
import Tree from 'components/flights/tree/Tree';

import showPage from 'actions/showPage';

class Flights extends React.Component {
    componentDidMount() {
        if ((this.props.viewType === 'table')) {
            this.props.showPage('flightsTableShow');
        }
    }

    componentDidUpdate(prevProps, prevState) {
        if ((this.props.viewType === 'table')) {
            this.props.showPage('flightsTableShow');
        }
    }

    buildFlightView() {
        if ((this.props.viewType === 'table')) {
            return <div id='container'></div>;
        } else {
            return <Tree/>;
        }
    }

    render () {
        return (
            <div>
                <MainPage/>
                <Toolbar viewType={ this.props.viewType }/>
                { this.buildFlightView() }
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        viewType: ownProps.match.params.viewType || 'tree'
    };
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPage, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Flights);
