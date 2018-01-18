import './spreadsheet.sass'

import React from 'react';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import SpreadsheetRow from 'components/calibration-form/spreadsheet-row/SpreadsheetRow';
import SpreadsheetTitle from 'components/calibration-form/spreadsheet-title/SpreadsheetTitle';

export default function Spreadsheet(props) {
  function handleClick() {
    props.update('xy', -1, 'add');
  }

  return (
    <div className='calibration-form-spreadsheet'>
      <SpreadsheetTitle/>
      {
        props.xy.map((item, index) =>
          <SpreadsheetRow
            paramId={ props.paramId }
            key={ index }
            index={ index }
            x={ parseInt(item.x) }
            y={ parseInt(item.y) }
            update={ props.update }
          />
        )
      }
      <div className='btn btn-default calibration-form-spreadsheet__buttom'
        onClick={ handleClick }
      >
        <Translate value='calibrationForm.spreadsheet.addButton'/>
      </div>
    </div>
  );
}

Spreadsheet.propTypes = {
  paramId: PropTypes.number.isRequired,
  xy: PropTypes.array.isRequired,
  update: PropTypes.func
};
