import './spreadsheet.sass'

import React from 'react';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import SpreadsheetRow from 'components/calibration-form/spreadsheet-row/SpreadsheetRow';

export default function Spreadsheet(props) {
    return (
        <div className='calibration-form-spreadsheet'>
            {
                props.xy.map((item, index) =>
                    <SpreadsheetRow
                        key={ index }
                        x={ parseInt(item.x) }
                        y={ parseInt(item.y) }
                    />
                )
            }
            <div className='btn btn-default calibration-form-spreadsheet__buttom'>
                <Translate value='calibrationForm.spreadsheet.addButton'/>
            </div>
        </div>
    );
}

Spreadsheet.propTypes = {
    xy: PropTypes.array.isRequired
};
