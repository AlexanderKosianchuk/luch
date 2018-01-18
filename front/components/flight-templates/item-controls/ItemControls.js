import './item-controls.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import redirect from 'actions/redirect';
import request from 'actions/request';

class ItemControls extends React.Component {
  handlePencilClick () {
    this.props.redirect('/flight-template-edit/update/'
      + 'flight-id/'+ this.props.flightId + '/'
      + 'template-name/'+ this.props.templateName
    );
  }

  handlePictureClick () {
    this.props.redirect('/chart/'
      + 'flight-id/'+ this.props.flightId + '/'
      + 'template-name/'+ this.props.templateName + '/'
      + 'from-frame/'+ this.props.startFrame + '/'
      + 'to-frame/'+ this.props.endFrame
    );
  }

  handleTrashClick () {
    this.props.request(
      ['templates', 'removeTemplate'],
      'delete',
      'TEMPLATE',
      {
        flightId: this.props.flightId,
        templateName: this.props.templateName
      }
    );
  }

  render () {
    let controls = [];

    let pictureButton = <button key={ 'picture' } onClick={ this.handlePictureClick.bind(this) }
        className='btn btn-default flight-templates-item-controls__button'>
      <span className='glyphicon glyphicon-picture flight-templates-item-controls__button-glyphicon'></span>
    </button>;

    let pencilButton = <button key={ 'pencil' } onClick={ this.handlePencilClick.bind(this) }
        className='btn btn-default flight-templates-item-controls__button'>
      <span className='glyphicon glyphicon-pencil flight-templates-item-controls__button-glyphicon'></span>
    </button>;

    let duplicateButton = '';/*<button key={ 'duplicate' } className='btn btn-default flight-templates-item-controls__button'>
      <span className='glyphicon glyphicon-duplicate flight-templates-item-controls__button-glyphicon'></span>
    </button>;*/

    let trashButton = <button key={ 'trash' }  onClick={ this.handleTrashClick.bind(this) }
        className='btn btn-default flight-templates-item-controls__button'>
      <span className='glyphicon glyphicon-trash flight-templates-item-controls__button-glyphicon'></span>
    </button>;

    if (!this.props.servicePurpose) {
      controls = Array(pictureButton, duplicateButton, pencilButton, trashButton);
    } else if (this.props.servicePurpose.isEvents || this.props.servicePurpose.isLast) {
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
    startFrame: state.flight.selectedStartFrame,
    endFrame: state.flight.selectedEndFrame
  };
}

function mapDispatchToProps(dispatch) {
  return {
    redirect: bindActionCreators(redirect, dispatch),
    request: bindActionCreators(request, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ItemControls);
