import './flight-importer-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import FileInput from 'react-file-input';
import { Translate, I18n } from 'react-redux-i18n';
import uuidV4 from 'uuid/v4';

import trigger from 'actions/trigger';

class FlightImporterDropdown extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isShown: false
        };
    }

    handleClickOutside(event) {
        if ((event.target.nodeName !== 'svg')
            && !event.target.ownerSVGElement
            && (event.target.className.includes('flight-importer-dropdown-toggle'))
            && !this.state.isShown
        ) {
            this.setState({ isShown: true });
            return;
        }

        this.setState({ isShown: false });
    }

    handleChange() {
        this.setState({ isShown: false });

        let guidInput = document.createElement('input');
        guidInput.name = 'flightGuid';
        guidInput.value = uuidV4().substring(0, 18).replace(/-/g, '');
        this.importFlightForm.appendChild(guidInput);

        let form = new FormData(this.importFlightForm);

        this.props.trigger('importItem', [form]);
    }

    render() {
        return (
            <ul className={ "flight-importer-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
              <li><a href="#"><b><Translate value='topMenu.flightImporterDropdown.fileImport'/></b></a></li>
              <li><a href="#">
                  <form action="" ref={ (form) => { this.importFlightForm = form; }}>
                      <FileInput
                         className="btn btn-default"
                         name="flightFileArchive"
                         placeholder={ I18n.t('topMenu.flightImporterDropdown.chooseFile') }
                         value={ this.state.file }
                         onChange={ this.handleChange.bind(this) }
                       />
                  </form>
             </a></li>
            </ul>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        trigger: bindActionCreators(trigger, dispatch),
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(onClickOutside(FlightImporterDropdown));
