import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'components/main-page/MainPage';
//import FlightListOptions from 'components/flight-list-options/FlightListOptions';

import getTemplateAction from 'actions/getTemplate';
import getFlightInfoAction from 'actions/getFlightInfo';
import showPageAction from 'actions/showPage';

class Chart extends React.Component {
    componentDidMount() {
        Promise.all([
            this.props.getTemplate({
                flightId: this.props.flightId,
                templateName: this.props.templateName
            }),
            this.props.getFlightInfo({ flightId: this.props.flightId })
        ]).then(() => {
            this.props.showPage('chartShow', [
                this.props.flightId,
                this.props.templateName,
                this.props.stepLength,
                this.props.startFlightTime,
                this.props.fromFrame,
                this.props.toFrame,
                this.props.activeTemplate.a,
                this.props.activeTemplate.b
            ]);
        });
    }

    render () {
        return (
            <div>
                <MainPage/>
                <div id='container'></div>
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.id,
        templateName: ownProps.match.params.templateName,
        fromFrame: ownProps.match.params.fromFrame,
        toFrame: ownProps.match.params.toFrame,
        activeTemplate: state.templates.activeTemplate,
        stepLength: state.flightInfo.stepLength,
        startFlightTime: state.flightInfo.startFlightTime,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPageAction, dispatch),
        getTemplate: bindActionCreators(getTemplateAction, dispatch),
        getFlightInfo: bindActionCreators(getFlightInfoAction, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Chart);
