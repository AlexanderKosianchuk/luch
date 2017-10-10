import './spreadsheet-title.sass'

import React from 'react';
import { Translate } from 'react-redux-i18n';

export default function SpreadsheetTitle () {
    return (
        <div className='calibration-form-spreadsheet-title'>
            <span className='calibration-form-spreadsheet-title__label'>
                <Translate value='calibrationForm.spreadsheetTitle.code'/>
            </span>
            <span className='calibration-form-spreadsheet-title__label'>
                <Translate value='calibrationForm.spreadsheetTitle.physics'/>
            </span>
            <span className='calibration-form-spreadsheet-title__dummy'></span>
        </div>
    );
}
