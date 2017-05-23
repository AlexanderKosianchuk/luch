import 'react-bootstrap-slider/src/css/bootstrap-slider.min.css';
import './flight-view-options-slider.sass';

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import ReactBootstrapSlider from 'react-bootstrap-slider';

import getFlightInfoAction from 'actions/getFlightInfo';

class FlightViewOptionsSlider extends React.Component {
    buildBody()
    {
        if (this.props.flightInfoPending === false) {
            return this.buildSlider();
        }

        return '';
    }

    buildSlider()
    {
        return <ReactBootstrapSlider
            value={ [0, this.props.flightDuration] }
            step={ this.props.stepLength }
            max={ this.props.flightDuration }
            min={ 0 }
            tooltip='hide'
            orientation='horizontal'
            handle='square'
            range={true}
         />;
    }

    render() {
        if (this.props.flightDuration === null) {
            this.props.getFlightInfo({flightId: this.props.flightId});
        }

        return (
            <ul className="flight-view-options-slider nav navbar-nav">
                <li>
                    { this.buildBody.apply(this) }
                </li>
            </ul>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightInfoPending: state.flightInfo.pending,
        flightDuration: state.flightInfo.duration,
        stepLength: state.flightInfo.stepLength
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightInfo: bindActionCreators(getFlightInfoAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightViewOptionsSlider);
