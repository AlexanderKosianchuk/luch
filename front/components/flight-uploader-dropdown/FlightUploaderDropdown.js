import './flight-uploader-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import FileInput from 'react-file-input';
import Guid from 'guid';

import FlightUploaderDropdownLoading from 'components/flight-uploader-dropdown-loading/FlightUploaderDropdownLoading';
import FlightUploaderFdrSelector from 'components/flight-uploader-fdr-selector/FlightUploaderFdrSelector';
import FlightUploaderCalibrationSelector from 'components/flight-uploader-calibration-selector/FlightUploaderCalibrationSelector';
import getFdrListAction from 'actions/getFdrList';
import flightUploaerChangeFdrTypeAction from 'actions/flightUploaderChangeFdrType';
import flightUploaderChangeCalibrationAction from 'actions/flightUploaderChangeCalibration';
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
        let uploadingUid = Guid.create();
        form.append('fdrId', this.props.selectedFdrType.id);
        form.append('calibrationId', this.props.selectedCalibration.id);
        /* just guid file name for progress reporting */
        form.append('progressFileName', uploadingUid);

        this.props.startEasyUploading({
            form: form,
            uploadingUid: uploadingUid
        });
        this.setState({
            isShown: false,
            file: ''
        });
    }

    render() {
        return (
            <ul className={ "flight-uploader-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
                <li><a href="#"><b>{ this.props.i18n.flightUploading }</b></a></li>
                { this.putFdrList() }
                { this.putCalibrationList() }
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
    }
}

function mapDispatchToProps(dispatch) {
    return {
        getFdrList: bindActionCreators(getFdrListAction, dispatch),
        changeFdrType: bindActionCreators(flightUploaerChangeFdrTypeAction, dispatch),
        changeCalibration: bindActionCreators(flightUploaderChangeCalibrationAction, dispatch),
        startEasyUploading: bindActionCreators(startEasyFlightUploadingAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(onClickOutside(FlightUploaderDropdown));
