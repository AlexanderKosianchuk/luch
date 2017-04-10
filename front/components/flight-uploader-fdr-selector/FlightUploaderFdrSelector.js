import './flight-uploader-fdr-selector.sass';

import React from 'react';

import Select from 'react-select2-wrapper';
import 'react-select2-wrapper/css/select2.min.css';

export default function FlightUploaderFdrSelector(props) {
    let selectFdrType = null;
    let fdrTypesList = props.fdrTypesList;

    function buildList() {
        if (!fdrTypesList || fdrTypesList.length === 0) {
            return [];
        }

        let list = [];
        for (var num in fdrTypesList) {
            if (fdrTypesList.hasOwnProperty(num)) {
                list.push({
                    text: fdrTypesList[num].name,
                    id: fdrTypesList[num].id
                });
            }
        }
        return list;
    }

    function handleSelect() {
        if (!selectFdrType.el[0]) {
            return;
        }

        let el = selectFdrType.el[0];
        let val = parseInt(el.options[el.selectedIndex].value);

        for (var num in fdrTypesList) {
            if (fdrTypesList.hasOwnProperty(num)
                && (fdrTypesList[num].id === val)
            ) {
                props.changeFdrType(fdrTypesList[num]);
            }
        }
    }

    return (
      <li className="flight-uploader-fdr-selector">
          <a href="#"><Select
              data={ buildList() }
              value={ props.defaultFdr.id }
              onSelect={ handleSelect }
              ref={(select) => { selectFdrType = select; }}
            />
          </a>
      </li>
    );
}
