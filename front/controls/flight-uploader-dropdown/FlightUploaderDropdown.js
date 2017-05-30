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
import FlightUploaderFdrSelector from 'controls/flight-uploader-fdr-selector/FlightUploaderFdrSelector';
import FlightUploaderCalibrationSelector from 'controls/flight-uploader-calibration-selector/FlightUploaderCalibrationSelector';

import getFdrList from 'actions/getFdrList';
import flightUploaerChangeFdrType from 'actions/flightUploaderChangeFdrType';
import flightUploaderChangeCalibration from 'actions/flightUploaderChangeCalibration';
import flightUploaderChangePreviewNeedState from 'actions/flightUploaderChangePreviewNeedState';
import startEasyFlightUploading from 'actions/startEasyFlightUploading';
import sendFlightFile from 'actions/sendFlightFile';
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
        if (!this.props.fdrTypesList) {
            this.props.getFdrList();
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
        if (this.props.fdrTypesList
            && (this.props.fdrTypesList.length > 0)
            && this.props.selectedFdrType
        ) {
            return <FlightUploaderFdrSelector
                defaultFdr={ this.props.selectedFdrType }
                fdrTypesList={ this.props.fdrTypesList }
                changeFdrType={ this.props.changeFdrType.bind(this) }
            />;
        }

        return '';
    }

    putCalibrationList() {
        if (this.props.selectedFdrType
            && this.props.selectedFdrType.calibrations
            && (this.props.selectedFdrType.calibrations.length > 0)
        ) {
            return <FlightUploaderCalibrationSelector
                defaultCalibration={ this.props.selectedCalibration }
                calibrations={ this.props.selectedFdrType.calibrations }
                changeCalibration={ this.props.changeCalibration.bind(this) }
            />;
        }

        return '';
    }

    handleChange() {
        let form = new FormData(this.sendFlightForm);
        let uploadingUid = uuidV4();
        let that = this;
        form.append('uploadingUid', uploadingUid);

        if (this.props.previewState) {
            this.props.sendFlightFile(form).then(() => {
                that.props.redirect('/uploading/' + uploadingUid
                    + '/fdr-id/' + this.props.selectedFdrType.id
                    + (this.props.selectedCalibration.id
                        ? ('/calibration-id/' + this.props.selectedCalibration.id)
                        : '')
                );
            });
        } else {
            form.append('fdrId', this.props.selectedFdrType.id);
            form.append('calibrationId', this.props.selectedCalibration.id);

            this.props.startEasyUploading({
                form: form,
                fdrId: this.props.selectedFdrType.id,
                calibrationId: this.props.selectedCalibration.id,
                uploadingUid: uploadingUid
            });
        }

        this.setState({
            isShown: false,
            file: ''
        });
    }

    handleSwitchChange(event, state) {
        this.props.changePreviewNeedState(state);
    }

    buildBody() {
        if (!this.props.fdrTypesListPending) {
            return <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
                <li><a href="#"><b><Translate value='flightUploaderDropdown.flightUploading'/></b></a></li>
                { this.putFdrList() }
                { this.putCalibrationList() }
                <li><a href="#">
                    <span className="flight-uploader-dropdown__switch-label"><Translate value='flightUploaderDropdown.preview'/></span>
                    <Switch
                        value={ this.props.previewState }
                        bsSize="mini"
                        onText={ <Translate value='flightUploaderDropdown.on'/> }
                        offText={ <Translate value='flightUploaderDropdown.off'/> }
                        onChange={ this.handleSwitchChange.bind(this) }
                    />
                </a></li>
                <li><a href="#">
                    <form action="" ref={ (form) => { this.sendFlightForm = form; }}>
                        <FileInput
                           className="btn btn-default"
                           name="flightFile"
                           placeholder={ I18n.t('flightUploaderDropdown.chooseFile') }
                           value={ this.state.file }
                           onChange={ this.handleChange.bind(this) }
                         />
                    </form>
               </a></li>
            </ul>
        }

        return <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
            <li><a href="#"><b><Translate value='flightUploaderDropdown.flightUploading'/></b></a></li>
            <li><ContentLoader margin={ 5 } size={ 75 } /></li>
        </ul>;
    }

    render() {
        return this.buildBody();
    }
}

function mapStateToProps (state) {
    return {
        fdrTypesListPending: state.fdrTypesList.pending,
        fdrTypesList: state.fdrTypesList.items,
        selectedFdrType: state.flightUploader.selectedFdrType,
        selectedCalibration: state.flightUploader.selectedCalibration,
        previewState: state.flightUploader.preview
    }
}

function mapDispatchToProps(dispatch) {
    return {
        getFdrList: bindActionCreators(getFdrList, dispatch),
        changeFdrType: bindActionCreators(flightUploaerChangeFdrType, dispatch),
        changeCalibration: bindActionCreators(flightUploaderChangeCalibration, dispatch),
        changePreviewNeedState: bindActionCreators(flightUploaderChangePreviewNeedState, dispatch),
        startEasyUploading: bindActionCreators(startEasyFlightUploading, dispatch),
        sendFlightFile: bindActionCreators(sendFlightFile, dispatch),
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(onClickOutside(FlightUploaderDropdown));
