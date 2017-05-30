import './flight-uploader-calibration-selector.sass';

import React from 'react';

import Select from 'react-select2-wrapper';
import 'react-select2-wrapper/css/select2.min.css';

export default function FlightUploaderCalibrationSelector(props) {
    let selectedCalibration = null;

    function buildList(calibrationList) {
        if (!calibrationList || calibrationList.length === 0) {
            return [];
        }

        let list = [];
        for (var num in calibrationList) {
            if (calibrationList.hasOwnProperty(num)) {
                list.push({
                    text: calibrationList[num].name,
                    id: calibrationList[num].id
                });
            }
        }
        return list;
    }

    function handleSelect() {
        if (!selectedCalibration.el[0]) {
            return;
        }

        let el = selectedCalibration.el[0];
        props.changeCalibration(el.options[el.selectedIndex].value);
    }

    return (
      <li className="flight-uploader-calibration-selector">
          <a href="#"><Select
              data={ buildList(props.calibrations) }
              value={ props.defaultCalibration.id }
              onSelect={ handleSelect }
              ref={(select) => { selectedCalibration = select; }}
            />
          </a>
      </li>
    );
}
