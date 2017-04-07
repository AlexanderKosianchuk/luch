import './flight-uploader-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import FileInput from 'react-file-input';

import FlightUploaderDropdownLoading from 'components/flight-uploader-dropdown-loading/FlightUploaderDropdownLoading';
import FlightUploaderFdrSelector from 'components/flight-uploader-fdr-selector/FlightUploaderFdrSelector';
import FlightUploaderCalibrationSelector from 'components/flight-uploader-calibration-selector/FlightUploaderCalibrationSelector';
import getFdrListAction from 'actions/getFdrList';

class FlightUploaderDropdown extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isShown: false,
            chosenFdr: null,
            defaultCalibration: null
        };
    }

    componentWillReceiveProps(nextProps) {
        let defaultFdr = this.setDefaultFdr(nextProps.fdrTypesList) || null;
        let defaultCalibration = defaultFdr
            ? this.setDefaultCalibration(defaultFdr.calibrations)
            : null;

        this.setState({
            chosenFdr: defaultFdr,
            defaultCalibration: defaultCalibration
        });
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
        ) {
            content = <FlightUploaderFdrSelector
                defaultFdr={ this.state.chosenFdr }
                fdrTypesList={ this.props.fdrTypesList }
                changeFdr={ this.changeFdr.bind(this) }
            />;
        }
        return content;
    }

    putCalibrationList() {
        if (!this.state.chosenFdr) {
            return '';
        }

        return <FlightUploaderCalibrationSelector
            defaultCalibration={ this.state.defaultCalibration }
            calibrations={ this.state.chosenFdr.calibrations }
            changeCalibration={ this.changeCalibration.bind(this) }
        />;
    }

    changeFdr(item) {
        this.setState({
            chosenFdr: item
        });
    }

    changeCalibration(item) {
        console.log(item);
    }

    handleChange() {
        let form = new FormData(this.sendFlightForm);
        /*!!!!*/
        this.setState({ isShown: false });
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
                           value=""
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
        fdrTypesList: state.fdrTypesList
    }
}

function mapDispatchToProps(dispatch) {
    return {
        getFdrList: bindActionCreators(getFdrListAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(onClickOutside(FlightUploaderDropdown));
