import React from 'react';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import Spreadsheet from 'components/calibration-form/spreadsheet/Spreadsheet';

export default function Param(props) {
    return (
        <div className='calibration-form-param'>
            <div className='row'>
                <div className='col-md-6'>
                    <div className='row'>
                        <div><b>
                            <Translate value='calibrationForm.param.code'/>:{' '}
                            { props.param.description.code }</b>
                        </div>
                        <div><u>
                            <Translate value='calibrationForm.param.name'/>:{' '}
                            { props.param.description.name }
                            { ' ' + '(' + props.param.description.dim + ')' }
                        </u></div>
                        <div>
                            <Translate value='calibrationForm.param.channels'/>:{' '}
                            { props.param.description.channel }
                        </div>
                        <div>
                            <Translate value='calibrationForm.param.minValue'/>:{' '}
                            { props.param.description.minValue } {' '}
                            { props.param.description.dim }
                        </div>
                        <div>
                            <Translate value='calibrationForm.param.maxValue'/>:{' '}
                            { props.param.description.maxValue } {' '}
                            { props.param.description.dim }
                        </div>
                    </div>
                    <div className='row'>
                        <Spreadsheet
                            xy={ props.param.xy || props.param.description.xy }
                        />
                    </div>
                </div>
                <div className='col-md-6'>
                        { props.param.id }
                </div>
            </div>
        </div>
    );
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
