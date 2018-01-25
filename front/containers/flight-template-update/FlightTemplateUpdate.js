import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import FlightTemplateEditToolbar from 'controls/flight-template-edit-toolbar/FlightTemplateEditToolbar';
import Params from 'components/flight-template-update/params/Params';

import Menu from 'controls/menu/Menu';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';

class FlightTemplateUpdate extends Component {
  componentDidMount() {
    if ((this.props.passedTemplateId !== this.props.storedTemplateId)) {
      this.props.request(
        ['flightTemplate', 'get'],
        'get',
        'FLIGHT_TEMPLATE',
        {
          flightId: this.props.flightId,
          templateId: this.props.passedTemplateId
        }
      );
    }
  }

  buildBody() {
    if ((this.props.passedTemplateId !== this.props.storedTemplateId)) {
      return <ContentLoader/>;
    } else {
      return (<span>
        <FlightTemplateEditToolbar
          flightId={ this.props.flightId }
          templateId={ this.props.passedTemplateId }
          templateName={ this.props.templateName }
        />
        <Params
          flightId={ this.props.flightId }
          colorPickerEnabled={ false }
        />
      </span>);
    }
  }

  render () {
    return (
      <div>
        <Menu/>
        { this.buildBody() }
      </div>
    );
  }
}

function mapStateToProps(state, ownProps) {
  return {
    templatePending: state.flightTemplate.pending,
    flightId: ownProps.match.params.flightId,
    passedTemplateId: ownProps.match.params.templateId,
    storedTemplateId: state.flightTemplate.id,
    templateName: state.flightTemplate.name
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplateUpdate);
