import 'react-bootstrap-slider/src/css/bootstrap-slider.min.css';
import './flight-range-slider.sass';

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import ReactBootstrapSlider from 'react-bootstrap-slider';

import get from 'actions/get';
import transmit from 'actions/transmit';

class FlightRangeSlider extends React.Component {
    componentWillMount() {
        if (this.props.flightDuration === null) {
            this.props.get(
                'flights/getFlightInfo',
                'FLIGHT',
                { flightId: this.props.flightId }
            );
        }
    }

    buildBody() {
        if (this.props.pending === false) {
            return this.buildSlider();
        }

        return '';
    }

    buildSlider() {
        return <ReactBootstrapSlider
            value={ [
                this.props.selectedStartFrame,
                this.props.selectedEndFrame
            ] }
            change={ this.changeSlider.bind(this) }
            step={ this.props.stepLength }
            min={ 0 }
            max={ this.props.flightDuration }
            tooltip='hide'
            orientation='horizontal'
            handle='square'
            range={true}
         />;
    }

    changeSlider(event) {
        if (event.target.value[0] !== this.props.selectedStartFrame) {
            this.props.transmit(
                'CHANGE_SELECTED_START_FRAME',
                event.target.value[0]
            );
        }

        if (event.target.value[1] !== this.props.selectedEndFrame) {
            this.props.transmit(
                'CHANGE_SELECTED_END_FRAME',
                event.target.value[1]
            );
        }
    }

    setStartTime() {
        if (this.props.selectedStartFrame === null) {
            return '';
        }

        return <a href='#'>
            { this.framesToTime(this.props.selectedStartFrame, this.props.stepLength) }
        </a>;
    }

    setEndTime() {
        if (this.props.selectedEndFrame === null) {
            return '';
        }

        return <a href='#'>
            { this.framesToTime(this.props.selectedEndFrame, this.props.stepLength) }
        </a>;
    }

    framesToTime(frames, stepLength) {
        let value = frames * stepLength;
        var secNum = parseInt(value, 10);
        var hours = Math.floor(secNum / 3600);
        var minutes = Math.floor((secNum - (hours * 3600)) / 60);
        var seconds = secNum - (hours * 3600) - (minutes * 60);

        if (hours < 10) {hours   = '0' + hours;}
        if (minutes < 10) {minutes = '0' + minutes;}
        if (seconds < 10) {seconds = '0' + seconds;}
        var time = hours+':'+minutes+':'+seconds;
        return time;
    }

    render() {
        return (
            <ul className='flight-range-slider nav navbar-nav'>
                <li>
                    { this.setStartTime.apply(this) }
                </li>
                <li>
                    <a href='#'>{ this.buildBody.apply(this) }</a>
                </li>
                <li>
                    { this.setEndTime.apply(this) }
                </li>
            </ul>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        pending: state.flight.pending,
        flightDuration: state.flight.duration,
        stepLength: state.flight.stepLength,
        selectedStartFrame: state.flight.selectedStartFrame,
        selectedEndFrame: state.flight.selectedEndFrame
    };
}

function mapDispatchToProps(dispatch) {
    return {
        get: bindActionCreators(get, dispatch),
        transmit: bindActionCreators(transmit, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightRangeSlider);
