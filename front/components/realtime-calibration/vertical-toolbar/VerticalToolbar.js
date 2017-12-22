import './vertical-toolbar.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import FdrSelector from 'controls/fdr-selector/FdrSelector';
import CalibrationSelector from 'controls/calibration-selector/CalibrationSelector';

import request from 'actions/request';

const TOP_MENU_HEIGHT = 51;
const MIN_WINDOW_WIDTH = 768; // check is mobile

class VerticalToolbar extends Component {
    static form = null;

    constructor(props) {
        super(props);

        this.state = {
            sources: ['']
        }
    }

    componentDidMount() {
        if (window.innerWidth > MIN_WINDOW_WIDTH) {
            this.form.style.height = (window.innerHeight - TOP_MENU_HEIGHT) + 'px';
        }
    }

    buildIpsInputs() {
        let ips = [];
        console.log(this.state.sources);
        for (let ii = 0; ii < this.state.sources.length; ii++) {
            ips.push(<input
                key={ ii }
                className='form-control'
                name='ip[]'
                value={ this.state.sources[ii] }
                onChange={ this.handleChange.bind(this, ii) }
                />
            );
        }

        return ips;
    }

    handleChange(index, event) {
        let sources = this.state.sources.slice();
        sources[index] = event.target.value;

        this.setState({ sources: sources });
    }

    handleClick(event) {
        event.preventDefault();

        let sources = this.state.sources.slice();
        sources.push('');

        this.setState({ sources: sources });
    }

    render() {
        return (
            <form
                className='realtime-calibration-vertical-toolbar'
                ref={ (form) => { this.form = form; } }
            >
                <div className='realtime-calibration-vertical-toolbar__connection-params'>
                    <Translate value='realtimeCalibration.verticalToolbar.connectionParams'/>
                </div>
                <div className='realtime-calibration-vertical-toolbar__label'>
                    <Translate value='realtimeCalibration.verticalToolbar.fdrType'/>
                </div>
                <div>
                    <ul className='realtime-calibration-vertical-toolbar__fdr-type'>
                        <FdrSelector />
                        <CalibrationSelector/>
                    </ul>
                </div>
                <div className='realtime-calibration-vertical-toolbar__label'>
                    <Translate value='realtimeCalibration.verticalToolbar.fdrType'/>
                </div>
                <div className='realtime-calibration-vertical-toolbar__controll'>
                    <select className='form-control' name='connectionType'>
                        <option value='udp'>UDP</option>
                    </select>
                </div>
                <div className='realtime-calibration-vertical-toolbar__label'>
                    <Translate value='realtimeCalibration.verticalToolbar.fdrType'/>
                </div>
                <div className='realtime-calibration-vertical-toolbar__controll'>
                    { this.buildIpsInputs() }
                </div>
                <div className='realtime-calibration-vertical-toolbar__button'>
                    <button
                        className='btn btn-default'
                        onClick={ this.handleClick.bind(this) }
                    >
                        <Translate value='realtimeCalibration.verticalToolbar.fdrType'/>
                    </button>
                </div>
            </form>
        );
    }
}

function mapStateToProps() {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(VerticalToolbar);
