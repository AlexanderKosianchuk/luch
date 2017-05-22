import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';

import showPageAction from 'actions/showPage';

class Calibrations extends React.Component {
    componentDidMount() {
        this.props.showPage('calibrationFormShow');
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
        showPage: bindActionCreators(showPageAction, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(Calibrations);
