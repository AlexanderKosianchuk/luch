import './param.sass'

import React, { Component } from 'react';
import { Translate, I18n } from 'react-redux-i18n';
import PropTypes from 'prop-types';

export default function Param(props) {
    return (
        <div className='calibration-form-param'>
            { props.param.id }
        </div>
    );
}

Param.propTypes = {
    param: PropTypes.shape({
        calibrationId: PropTypes.number,
        // description: PropTypes.shape({
        //     channel:"115,243"
        //     code:"UIL"
        //     color:"2a72bb"
        //     dim:"В"
        //     id:42
        //     k:1
        //     mask:65535
        //     maxValue:0
        //     minValue:0
        //     minus:0
        //     name:"Напряжение постоянн.тока лев."
        //     prefix:"2"
        //     shift:8
        //     type:21
        // }),
        id: PropTypes.number,
        paramId: PropTypes.number.isRequired,
        xy: PropTypes.array
    })
};
