import './flight-uploader-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import Switch from 'react-bootstrap-switch';
import 'react-bootstrap-switch/dist/css/bootstrap3/react-bootstrap-switch.min.css';
import FileInput from 'react-file-input';
import Guid from 'guid';

import FlightUploaderDropdownLoading from 'components/flight-uploader-dropdown-loading/FlightUploaderDropdownLoading';
import FlightUploaderFdrSelector from 'components/flight-uploader-fdr-selector/FlightUploaderFdrSelector';
import FlightUploaderCalibrationSelector from 'components/flight-uploader-calibration-selector/FlightUploaderCalibrationSelector';
import getFdrListAction from 'actions/getFdrList';
import flightUploaerChangeFdrTypeAction from 'actions/flightUploaderChangeFdrType';
import flightUploaderChangeCalibrationAction from 'actions/flightUploaderChangeCalibration';
import flightUploaderChangePreviewNeedStateAction from 'actions/flightUploaderChangePreviewNeedState';
import startEasyFlightUploadingAction from 'actions/startEasyFlightUploading';

class FlightUploaderDropdown extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isShown: false,
            file: '',
            chosenFdr: null,
            defaultCalibration: null
        };
    }

    setDefaultFdr (fdrTypesList) {
        if (!fdrTypesList
            || fdrTypesList.length === 0
            || !fdrTypesList[0].id
        ) {
            return null;
        }

        return fdrTypesList[0];
    }

    setDefaultCalibration (calibrationList) {
        if (!calibrationList
            || calibrationList.length === 0
            || !calibrationList[0].id
        ) {
            return [];
        }

        return calibrationList[0];
    }

    componentWillMount() {
        if (!this.props.fdrTypes) {
            this.props.getFdrList();
        }
    }

    componentWillReceiveProps(nextProps) {
        /* do not need render on switch change */
        if (nextProps.previewState !== this.props.previewState) {
            return false;
        }

        let defaultFdr = this.setDefaultFdr(nextProps.fdrTypesList) || null;
        let defaultCalibration = defaultFdr
            ? this.setDefaultCalibration(defaultFdr.calibrations)
            : null;

        this.props.changeFdrType(defaultFdr);
        this.props.changeCalibration(defaultCalibration);
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
        let content = <FlightUploaderDropdownLoading />;
        if (this.props.fdrTypesList
            && this.props.fdrTypesList[0] //at list one el exist
            && this.props.selectedFdrType
        ) {
            content = <FlightUploaderFdrSelector
                defaultFdr={ this.props.selectedFdrType }
                fdrTypesList={ this.props.fdrTypesList }
                changeFdrType={ this.props.changeFdrType.bind(this) }
            />;
        }
        return content;
    }

    putCalibrationList() {
        if (!this.props.selectedFdrType) {
            return '';
        }

        return <FlightUploaderCalibrationSelector
            defaultCalibration={ this.props.selectedCalibration }
            calibrations={ this.props.selectedFdrType.calibrations }
            changeCalibration={ this.props.changeCalibration.bind(this) }
        />;
    }

    handleChange() {
        let form = new FormData(this.sendFlightForm);
        let progressStatusFile = Guid.create() + '.prgs';

        if (this.props.previewState) {
            this.props.topMenuService.uploadWithPreview(
                form,
                progressStatusFile,
                this.props.selectedFdrType.id,
                this.props.selectedFdrType.name,
                this.props.selectedCalibration.id
            );
        } else {
            form.append('fdrId', this.props.selectedFdrType.id);
            form.append('calibrationId', this.props.selectedCalibration.id);
            /* just guid file name for progress reporting */
            form.append('progressFileName', uploadingUid);
            this.props.startEasyUploading({
                form: form,
                fdrId: this.props.selectedFdrType.id,
                calibrationId: this.props.selectedCalibration.id,
                progressStatusFile: progressStatusFile
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

    render() {
        return (
            <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
                <li><a href="#"><b>{ this.props.i18n.flightUploading }</b></a></li>
                { this.putFdrList() }
                { this.putCalibrationList() }
                <li><a href="#">
                    <span className="flight-uploader-dropdown__switch-label">Предпросмотр</span>
                    <Switch
                        value={ this.props.previewState }
                        bsSize="mini"
                        onText={ this.props.i18n.on }
                        offText={ this.props.i18n.off }
                        onChange={ this.handleSwitchChange.bind(this) }
                    />
                </a></li>
                <li><a href="#">
                    <form action="" ref={ (form) => { this.sendFlightForm = form; }}>
                        <FileInput
                           className="btn btn-default"
                           name="flightFile"
                           placeholder={ this.props.i18n.chooseFile }
                           value={ this.state.file }
                           onChange={ this.handleChange.bind(this) }
                         />
                    </form>
               </a></li>
            </ul>
        );
    }
}

function mapStateToProps (state) {
    return {
        fdrTypesList: state.fdrTypesList,
        selectedFdrType: state.flightUploader.selectedFdrType,
        selectedCalibration: state.flightUploader.selectedCalibration,
        previewState: state.flightUploader.preview
    }
}

function mapDispatchToProps(dispatch) {
    return {
        getFdrList: bindActionCreators(getFdrListAction, dispatch),
        changeFdrType: bindActionCreators(flightUploaerChangeFdrTypeAction, dispatch),
        changeCalibration: bindActionCreators(flightUploaderChangeCalibrationAction, dispatch),
        changePreviewNeedState: bindActionCreators(flightUploaderChangePreviewNeedStateAction, dispatch),
        startEasyUploading: bindActionCreators(startEasyFlightUploadingAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(onClickOutside(FlightUploaderDropdown));
