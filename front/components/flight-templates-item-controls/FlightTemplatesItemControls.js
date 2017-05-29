import './flight-templates-item-controls.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import redirect from 'actions/redirect';

class FlightTemplatesItemControls extends React.Component {
    handlePictureClick ()
    {
        this.props.redirect('/chart/'
            + 'flight-id/'+ this.props.flightId + '/'
            + 'template-name/'+ this.props.templateName + '/'
            + 'from-frame/'+ this.props.startFrame + '/'
            + 'to-frame/'+ this.props.endFrame
        );
    }

    render ()
    {
        let controls = [];

        let pictureButton = <button key={ 'picture' } onClick={ this.handlePictureClick.bind(this) } className='btn btn-default flight-templates-item-controls__button'>
            <span className='glyphicon glyphicon-picture flight-templates-item-controls__button-glyphicon'></span>
        </button>;

        let pencilButton = <button key={ 'pencil' } className='btn btn-default flight-templates-item-controls__button'>
            <span className='glyphicon glyphicon-pencil flight-templates-item-controls__button-glyphicon'></span>
        </button>;

        let duplicateButton = <button key={ 'duplicate' } className='btn btn-default flight-templates-item-controls__button'>
            <span className='glyphicon glyphicon-duplicate flight-templates-item-controls__button-glyphicon'></span>
        </button>;

        let trashButton = <button key={ 'trash' } className='btn btn-default flight-templates-item-controls__button'>
            <span className='glyphicon glyphicon-trash flight-templates-item-controls__button-glyphicon'></span>
        </button>;

        if (this.props.servicePurpose.isEvents || this.props.servicePurpose.isLast) {
            controls = Array(pictureButton, duplicateButton);
        } else if (this.props.servicePurpose.isDefault) {
            controls = Array(pictureButton, duplicateButton, pencilButton);
        } else {
            controls = Array(pictureButton, duplicateButton, pencilButton, trashButton);
        }

        return <div className='flight-templates-item-controls'>{ controls }</div>
    }
}

function mapStateToProps(state) {
    return {
        startFrame: state.flightInfo.selectedStartFrame,
        endFrame: state.flightInfo.selectedEndFrame
    };
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplatesItemControls);
