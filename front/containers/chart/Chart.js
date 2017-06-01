import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'controls/main-page/MainPage';
//import FlightListOptions from 'components/flight-list-options/FlightListOptions';

import getTemplate from 'actions/getTemplate';
import getFlightInfo from 'actions/getFlightInfo';
import showPage from 'actions/showPage';

class Chart extends React.Component {
    componentDidMount() {
        Promise.all([
            this.props.getTemplate({
                flightId: this.props.flightId,
                templateName: this.props.templateName
            }),
            this.props.getFlightInfo({ flightId: this.props.flightId })
        ]).then(() => {
            let analogParams = this.props.activeTemplate.ap || [];
            let binaryParams = this.props.activeTemplate.bp || [];

            let analogParamsCodes = [];
            let binaryParamsCodes = [];

            analogParams.forEach((item) => {
                analogParamsCodes.push(item.code);
            });

            binaryParams.forEach((item) => {
                binaryParamsCodes.push(item.code);
            });

            this.props.showPage('chartShow', [
                this.props.flightId,
                this.props.templateName,
                this.props.stepLength,
                this.props.startFlightTime,
                this.props.fromFrame,
                this.props.toFrame,
                analogParamsCodes,
                binaryParamsCodes
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
        flightId: ownProps.match.params.flightId,
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
        showPage: bindActionCreators(showPage, dispatch),
        getTemplate: bindActionCreators(getTemplate, dispatch),
        getFlightInfo: bindActionCreators(getFlightInfo, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Chart);
