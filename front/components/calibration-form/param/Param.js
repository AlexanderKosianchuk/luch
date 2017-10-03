import './param.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import Spreadsheet from 'components/calibration-form/spreadsheet/Spreadsheet';
import Chart from 'components/calibration-form/chart/Chart';

export default class Param extends Component {
    constructor(props) {
        super(props);

        let xy = this.sort(props.param.xy) || this.sort(props.param.description.xy);
        xy = xy.map((item) => {
            return {
                x: parseInt(item.x),
                y: parseInt(item.y)
            }
        });

        this.state = {
            chartWidth: 0,
            chartHeaight: 0,
            xy: xy
        }
    }

    componentDidMount() {
        if (this.state.chartWidth === 0) {
            this.setState({
                chartWidth: this.subling.offsetWidth,
                chartHeight: this.subling.offsetHeight - 30
            });
        }
    }

    sort(xy) {
        if (!Array.isArray(xy)) {
            return null;
        }

        return xy.sort((prev, next) => {
            return (parseInt(prev.y) < parseInt(next.y)) ? 1 : -1;
        });
    }

    update(key, index, value) {
        if (this.state.xy[index]
            && this.state.xy[index][key]
            && (this.state.xy[index][key] !== value)
        ) {
            let xy = this.state.xy.slice();
            xy[index][key] = value;

            this.setState({
                xy: xy
            });
        }
    }

    render() {
        return (
            <div className='calibration-form-param'>
                <div className='calibration-form-param__description'>
                    <div><b>
                        <Translate value='calibrationForm.param.code'/>:{' '}
                        { this.props.param.description.code }</b>
                    </div>
                    <div><u>
                        <Translate value='calibrationForm.param.name'/>:{' '}
                        { this.props.param.description.name }
                        { ' ' + '(' + this.props.param.description.dim + ')' }
                    </u></div>
                    <div>
                        <Translate value='calibrationForm.param.channels'/>:{' '}
                        { this.props.param.description.channel }
                    </div>
                    <div>
                        <Translate value='calibrationForm.param.minValue'/>:{' '}
                        { this.props.param.description.minValue } {' '}
                        { this.props.param.description.dim }
                    </div>
                    <div>
                        <Translate value='calibrationForm.param.maxValue'/>:{' '}
                        { this.props.param.description.maxValue } {' '}
                        { this.props.param.description.dim }
                    </div>
                </div>
                <div className='row'>
                    <div className='col-md-6' ref={ (subling) => { this.subling = subling }}>
                        <Spreadsheet
                            paramId={ this.props.param.id }
                            xy={ this.state.xy }
                            update={ this.update.bind(this) }
                        />
                    </div>
                    <div className='col-md-6'>
                        <Chart
                            width={ this.state.chartWidth }
                            height={ this.state.chartHeight }
                            data={ this.state.xy }
                            minValue={ parseInt(this.props.param.description.minValue) }
                            maxValue={ parseInt(this.props.param.description.maxValue) }
                        />
                    </div>
                </div>
            </div>
        );
    }
}

Param.propTypes = {
    param: PropTypes.shape({
        calibrationId: PropTypes.number,
        description: PropTypes.shape({
            id: PropTypes.number.isRequired,
            channel: PropTypes.string.isRequired,
            code: PropTypes.string.isRequired,
            color: PropTypes.string.isRequired,
            dim: PropTypes.string.isRequired,
            maxValue: PropTypes.number.isRequired,
            minValue: PropTypes.number.isRequired,
            name: PropTypes.string.isRequired
        }).isRequired,
        id: PropTypes.number,
        paramId: PropTypes.number.isRequired,
        xy: PropTypes.array
    })
};
