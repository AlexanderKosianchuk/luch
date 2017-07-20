import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';

import showPage from 'actions/showPage';

class Calibrations extends React.Component {
    componentDidMount() {
        this.props.showPage('calibrationsShow');
    }

    render () {
        return (
            <div>
                <Menu />
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

export default connect(() => { return {} }, mapDispatchToProps)(Calibrations);
