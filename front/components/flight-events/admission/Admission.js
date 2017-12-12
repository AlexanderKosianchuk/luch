import './admission.sass'

import React from 'react';
import PropTypes from 'prop-types';

import Checkbox from 'controls/checkbox/Checkbox';

export default function Admission(props) {
    return (
        <label className='flight-events-admission'>
            <div className='flight-events-admission__admissions-container'>
                <div className='flight-events-admission__admissions-label'>
                    { props.label }
                </div>
                <div className='flight-events-admission__admissions-box'>
                    <Checkbox
                        disabled={ props.disabled }
                        checkstate={ props.checkstate }
                        changeCheckState={ props.changeCheckState.bind(this) }
                    />
                </div>
            </div>
        </label>
    );
}

Admission.propTypes = {
    label: PropTypes.string.isRequired,
    disabled: PropTypes.bool,
    checkstate: PropTypes.bool.isRequired,
    changeCheckState: PropTypes.func.isRequired
};
