import './param.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import Spreadsheet from 'components/calibration-form/spreadsheet/Spreadsheet';
import Chart from 'components/calibration-form/chart/Chart';

export default class Param extends Component {
    constructor(props) {
        super(props);

        let xy = (props.param.xy.length > 0)
            ? props.param.description.xy
            : props.param.description.xy;

        xy = this.sort(xy);
        xy = xy.map((item) => {
            return {
                x: parseInt(item.x),
                y: parseInt(item.y)
            }
        });

        this.state = {
            chartWidth: 0,
            chartHeaight: 0,
            xy: xy,
            messageIsHidden: true
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
        if ((key === 'xy') && (value === null)) {
            let xy = this.state.xy.slice();
            xy.splice(index, 1);

            this.setState({
                xy: xy
            });

            return;
        }

        if ((key === 'xy')
            && (index === -1)
            && (value === 'add')
        ) {
            let xy = this.state.xy.slice();

            if (this.state.xy.length > 2) {
                let xy2 = this.state.xy.pop();
                let xy1 = this.state.xy.pop();

                let x = xy2.x > xy1.x
                    ? xy2.x + (xy2.x - xy1.x)
                    : xy2.x - (xy1.x - xy2.x);

                let y = this.getAproximation(xy2, xy1, 'y', 'x', x);

                xy.push({ x: x, y: y });
            } else if ((this.state.xy.length < 2)
                && (this.state.xy.length > 0)
            ) {
                let xy = this.state.xy.pop();

                xy.push({ x: xy.x * 1.1, y: xy.y * 1.1 });
            } else {
                xy.push({ x: 1, y: 1 });
            }

            this.setState({
                xy: xy
            });

            return;
        }

        if (this.state.xy[index]
            && this.state.xy[index][key]
            && (this.state.xy[index][key] !== value)
        ) {
            let aprox = null;
            let xy = this.state.xy.slice();
            xy[index][key] = value;

            let attr1 = 'x';
            let attr2 = 'y';
            let search = xy[index]['y'];

            if (key === 'y') {
                attr1 = 'y';
                attr2 = 'x';
                search = xy[index]['x'];
            }

            if (this.state.xy.length > 2) {
                let xy2 = this.state.xy[1];
                let xy1 = this.state.xy[2];

                if (index === (this.state.xy.length - 1)) {
                    let xy2 = this.state.xy[this.state.xy.length - 2];
                    let xy1 = this.state.xy[this.state.xy.length - 3];
                } else if (index !== 0) {
                    xy2 = this.state.xy[index + 1];
                    xy1 = this.state.xy[index - 1];
                }

                aprox = this.getAproximation(xy2, xy1, attr1, attr2, search);
            }

            this.setState({
                xy: xy,
                messageIsHidden: this.isBetween(value, aprox)
            });
        }
    }

    isBetween(val, aprox) {
        let min = Math.min(aprox * -2, aprox * 2);
        let max = Math.max(aprox * -2, aprox * 2);

        return ((val > min) ? ((val < max) ? val : max) : min) === val;
    }

    getAproximation(xy2, xy1, attr1, attr2, val) {
        return xy2[attr1]
            + (val - xy2[attr2])
            / (xy1[attr2] - xy2[attr2])
            * (xy1[attr1] - xy2[attr1]);
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
                    <div className={ 'calibration-form-param__message ' + (this.state.messageIsHidden ? 'is-hidden' : '') }>
                        <Translate value='calibrationForm.param.deviationFromStandardView'/>
                    </div>
                </div>
                <div className='row'>
                    <div className='col-md-6' ref={ (subling) => { this.subling = subling }}>
                        <Spreadsheet
                            paramId={ this.props.param.description.id }
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
