import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';

import showPage from 'actions/showPage';

class FlightsSearch extends React.Component {
    componentDidMount() {
        this.props.showPage('flightSearchFormShow');
    }

    render () {
        return (
            <div>
                <MainPage />
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

export default connect(() => { return {} }, mapDispatchToProps)(FlightsSearch);
