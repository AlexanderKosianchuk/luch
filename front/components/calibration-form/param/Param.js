import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import Spreadsheet from 'components/calibration-form/spreadsheet/Spreadsheet';
import Chart from 'components/calibration-form/chart/Chart';

export default class Param extends Component {
    constructor(props) {
        super(props);

        this.state = {
            xy: props.param.xy || props.param.description.xy
        }
    }

    render() {
        return (
            <div className='calibration-form-param'>
                <div className='row'>
                    <div className='col-md-6'>
                        <div className='row'>
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
                            <Spreadsheet
                                xy={ this.state.xy }
                            />
                        </div>
                    </div>
                    <div className='col-md-6'>
                        <Chart
                            xy={ this.state.xy }
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
