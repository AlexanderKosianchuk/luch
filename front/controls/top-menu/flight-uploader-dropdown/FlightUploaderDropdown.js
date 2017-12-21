import './flight-uploader-dropdown.sass';

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import Switch from 'react-bootstrap-switch';
import 'react-bootstrap-switch/dist/css/bootstrap3/react-bootstrap-switch.min.css';
import uuidV4 from 'uuid/v4';
import { Translate, I18n } from 'react-redux-i18n';

import FileInput from 'controls/file-input/FileInput';
import ContentLoader from 'controls/content-loader/ContentLoader';
import FdrSelector from 'controls/fdr-selector/FdrSelector';
import CalibrationSelector from 'controls/calibration-selector/CalibrationSelector';

import startEasyFlightUploading from 'actions/particular/startEasyFlightUploading';

import request from 'actions/request';
import redirect from 'actions/redirect';

class FlightUploaderDropdown extends Component {
    constructor(props) {
        super(props);

        this.state = {
            isShown: false,
            isReady: false,
            previewNeed: true,
            file: '',
        };
    }

    handleClickOutside(event) {
        if ((event.target.nodeName !== 'svg')
            && !event.target.ownerSVGElement
            && (event.target.className.includes('flight-uploader-dropdown-toggle'))
            && !this.state.isShown
        ) {
            this.setState({ isShown: true });
        } else {
            this.setState({ isShown: false });
        }
    }

    handleChange() {
        let form = new FormData(this.sendFlightForm);
        let uploadingUid = uuidV4().substring(0, 18).replace(/-/g, '');

        form.append('uploadingUid', uploadingUid);

        if (this.state.previewNeed) {
            this.props.request(
                ['uploader', 'storeFlightFile'],
                'post',
                'FLIGHT_FILE',
                form
            ).then(() => {
                this.props.redirect('/uploading/' + uploadingUid
                    + '/fdr-id/' + this.props.chosenFdr.id
                    + (this.props.chosenCalibration.id
                        ? ('/calibration-id/' + this.props.chosenCalibration.id || '')
                        : '')
                );
            });
        } else {
            form.append('fdrId', this.props.chosenFdr.id);
            form.append('calibrationId', this.props.chosenCalibration.id || '');

            this.props.startEasyUploading({
                form: form,
                fdrId: this.props.chosenFdr.id,
                calibrationId: this.props.chosenCalibration.id || '',
                uploadingUid: uploadingUid
            });
        }

        this.setState({
            isShown: false,
            file: ''
        });
    }

    handleSwitchChange(event, state) {
        this.setState({
            previewNeed: state,
        });
    }

    render() {
        return <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
            <li><a href="#"><b><Translate value='topMenu.flightUploaderDropdown.flightUploading'/></b></a></li>
            <FdrSelector />
            <CalibrationSelector/>
            <li><a href="#">
                <span className="flight-uploader-dropdown__switch-label"><Translate value='topMenu.flightUploaderDropdown.preview'/></span>
                <Switch
                    value={ this.state.previewNeed }
                    bsSize="mini"
                    onText={ <Translate value='topMenu.flightUploaderDropdown.on'/> }
                    offText={ <Translate value='topMenu.flightUploaderDropdown.off'/> }
                    onChange={ this.handleSwitchChange.bind(this) }
                />
            </a></li>
            <li><a href="#">
                <form action="" ref={ (form) => { this.sendFlightForm = form; }}
                    className={ this.state.isReady ? '' : 'flight-uploader-dropdown__is-disabled'}
                >
                    <FileInput
                       className="btn btn-default"
                       name="flightFile"
                       placeholder={ I18n.t('topMenu.flightUploaderDropdown.chooseFile') }
                       value={ this.state.file }
                       onChange={ this.handleChange.bind(this) }
                     />
                </form>
           </a></li>
        </ul>
    }
}

function mapStateToProps(state) {
    return {
        fdrsPending: state.fdrs.pending,
        fdrs: state.fdrs,
        chosenFdr: state.fdrs.chosen,
        chosenCalibration: state.calibrations.chosen
    }
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        redirect: bindActionCreators(redirect, dispatch),
        startEasyUploading: bindActionCreators(startEasyFlightUploading, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(onClickOutside(FlightUploaderDropdown));
