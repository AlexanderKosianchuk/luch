import './flight-uploader-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import Switch from 'react-bootstrap-switch';
import 'react-bootstrap-switch/dist/css/bootstrap3/react-bootstrap-switch.min.css';
import FileInput from 'react-file-input';
import uuidV4 from 'uuid/v4';
import { Translate, I18n } from 'react-redux-i18n';

import ContentLoader from 'controls/content-loader/ContentLoader';
import FlightUploaderFdrSelector from 'controls/top-menu/flight-uploader-fdr-selector/FlightUploaderFdrSelector';
import FlightUploaderCalibrationSelector from 'controls/top-menu/flight-uploader-calibration-selector/FlightUploaderCalibrationSelector';

import get from 'actions/get';
import transmit from 'actions/transmit';

import startEasyFlightUploading from 'actions/particular/startEasyFlightUploading';
import sendFlightFile from 'actions/particular/sendFlightFile';
import redirect from 'actions/redirect';

class FlightUploaderDropdown extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isShown: false,
            file: '',
        };
    }

    componentWillMount() {
        if (this.props.fdrsPending !== false) {
            this.props.get(
                'fdr/getFdrs',
                'FDRS'
            );
        }
    }

    handleClickOutside(event) {
        if ((event.target.className.includes('flight-uploader-dropdown-toggle'))
            && !this.state.isShown
        ) {
            this.setState({ isShown: true });
        } else {
            this.setState({ isShown: false });
        }
    }

    putFdrList() {
        if (this.props.fdrs
            && (this.props.fdrs.items.length > 0)
            && this.props.chosenFdr
        ) {
            return <FlightUploaderFdrSelector
                defaultFdr={ this.props.chosenFdr }
                fdrs={ this.props.fdrs.items }
                changeFdrType={ this.changeFdrType.bind(this) }
            />;
        }

        return '';
    }

    changeFdrType(payload) {
        this.props.transmit('CHOOSE_FDR', payload);

        if (payload.calibrations
            && (payload.calibrations.length > 0)
        ) {
            this.props.transmit('CHOOSE_CALIBRATION', payload.calibrations[0]);
        }

    }

    putCalibrationList() {
        if ((typeof this.props.chosenFdr === 'object')
            && (typeof this.props.chosenCalibration === 'object')
            && this.props.chosenFdr.calibrations
            && (this.props.chosenFdr.calibrations.length > 0)
        ) {
            return <FlightUploaderCalibrationSelector
                defaultCalibration={ this.props.chosenCalibration }
                calibrations={ this.props.chosenFdr.calibrations }
                changeCalibration={ this.changeCalibration.bind(this) }
            />;
        }

        return '';
    }

    changeCalibration(payload) {
        this.props.transmit('CHOOSE_CALIBRATION', payload);
    }

    handleChange() {
        let form = new FormData(this.sendFlightForm);
        let uploadingUid = uuidV4();
        let that = this;
        form.append('uploadingUid', uploadingUid);

        if (this.props.previewState) {
            this.props.sendFlightFile(form).then(() => {
                that.props.redirect('/uploading/' + uploadingUid
                    + '/fdr-id/' + this.props.chosenFdr.id
                    + (this.props.chosenCalibration.id
                        ? ('/calibration-id/' + this.props.chosenCalibration.id)
                        : '')
                );
            });
        } else {
            form.append('fdrId', this.props.chosenFdr.id);
            form.append('calibrationId', this.props.chosenCalibration.id);

            this.props.startEasyUploading({
                form: form,
                fdrId: this.props.chosenFdr.id,
                calibrationId: this.props.chosenCalibration.id,
                uploadingUid: uploadingUid
            });
        }

        this.setState({
            isShown: false,
            file: ''
        });
    }

    handleSwitchChange(event, state) {
        this.props.transmit('CHANGE_PREVIEW_NEED_STATE', state);
    }

    buildBody() {
        if (this.props.fdrsPending === false) {
            return <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
                <li><a href="#"><b><Translate value='topMenu.flightUploaderDropdown.flightUploading'/></b></a></li>
                { this.putFdrList() }
                { this.putCalibrationList() }
                <li><a href="#">
                    <span className="flight-uploader-dropdown__switch-label"><Translate value='topMenu.flightUploaderDropdown.preview'/></span>
                    <Switch
                        value={ this.props.previewState }
                        bsSize="mini"
                        onText={ <Translate value='topMenu.flightUploaderDropdown.on'/> }
                        offText={ <Translate value='topMenu.flightUploaderDropdown.off'/> }
                        onChange={ this.handleSwitchChange.bind(this) }
                    />
                </a></li>
                <li><a href="#">
                    <form action="" ref={ (form) => { this.sendFlightForm = form; }}>
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

        return <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
            <li><a href="#"><b><Translate value='topMenu.flightUploaderDropdown.flightUploading'/></b></a></li>
            <li><ContentLoader margin={ 5 } size={ 75 } /></li>
        </ul>;
    }

    render() {
        return this.buildBody();
    }
}

function mapStateToProps(state) {
    return {
        fdrsPending: state.fdrs.pending,
        fdrs: state.fdrs,
        chosenFdr: state.fdrs.chosen,
        chosenCalibration: state.fdrs.chosenCalibration,
        previewState: state.flightUploader.preview
    }
}

function mapDispatchToProps(dispatch) {
    return {
        get: bindActionCreators(get, dispatch),
        transmit: bindActionCreators(transmit, dispatch),
        startEasyUploading: bindActionCreators(startEasyFlightUploading, dispatch),
        sendFlightFile: bindActionCreators(sendFlightFile, dispatch),
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(onClickOutside(FlightUploaderDropdown));
