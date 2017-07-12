import './flight-uploader-fdr-selector.sass';

import React from 'react';

import Select from 'react-select2-wrapper';
import 'react-select2-wrapper/css/select2.min.css';

export default function FlightUploaderFdrSelector(props) {
    let selectFdrType = null;
    let fdrs = props.fdrs;

    function buildList() {
        if (!fdrs || fdrs.length === 0) {
            return [];
        }

        let list = [];
        for (var num in fdrs) {
            if (fdrs.hasOwnProperty(num)) {
                list.push({
                    text: fdrs[num].name,
                    id: fdrs[num].id
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

        for (var num in fdrs) {
            if (fdrs.hasOwnProperty(num)
                && (fdrs[num].id === val)
            ) {
                props.changeFdrType(fdrs[num]);
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
