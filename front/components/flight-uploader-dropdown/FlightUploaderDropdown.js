import './flight-uploader-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import Switch from 'react-bootstrap-switch';
import 'react-bootstrap-switch/dist/css/bootstrap3/react-bootstrap-switch.min.css';
import FileInput from 'react-file-input';
import uuidV4 from 'uuid/v4';

import ContentLoader from 'components/content-loader/ContentLoader';
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

        if (this.props.previewState) {
            this.props.topMenuService.uploadWithPreview(
                form,
                uploadingUid,
                this.props.selectedFdrType.id,
                this.props.selectedFdrType.name,
                this.props.selectedCalibration.id
            );
        } else {
            form.append('fdrId', this.props.selectedFdrType.id);
            form.append('calibrationId', this.props.selectedCalibration.id);
            /* just guid file name for progress reporting */
            form.append('uploadingUid', uploadingUid);
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
        }

        return <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
            <li><a href="#"><b>{ this.props.i18n.flightUploading }</b></a></li>
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
        getFdrList: bindActionCreators(getFdrListAction, dispatch),
        changeFdrType: bindActionCreators(flightUploaerChangeFdrTypeAction, dispatch),
        changeCalibration: bindActionCreators(flightUploaderChangeCalibrationAction, dispatch),
        changePreviewNeedState: bindActionCreators(flightUploaderChangePreviewNeedStateAction, dispatch),
        startEasyUploading: bindActionCreators(startEasyFlightUploadingAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(onClickOutside(FlightUploaderDropdown));
