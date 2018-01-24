import './list.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Collapse } from 'react-collapse';

import ContentLoader from 'controls/content-loader/ContentLoader';
import Item from 'components/flight-templates/item/Item';

import request from 'actions/request';
import transmit from 'actions/transmit';

class List extends Component {
  constructor(props) {
    super(props);
    this.state = {
      isOpened: false
    };
  }

  componentDidMount() {
    this.props.request(
      ['templates', 'getFlightTemplates'],
      'get',
      'FLIGHT_TEMPLATES',
      { flightId: this.props.flightId }
    ).then((resp) => {
      if (resp.length < 1) {
        return;
      }

      let defaultIndex = resp.findIndex((item)  => {
        return item.servicePurpose
          && item.servicePurpose.isDefault === true;
      });

      if (defaultIndex === -1) {
        return;
      }

      this.props.transmit(
        'CHOOSE_TEMPLATE',
        { id: resp[defaultIndex].id }
      );
    });
  }

  buildTemplatesList() {
    let list = [];
    this.props.flightTemplates.items.forEach((item, index) => {
      list.push(<Item
        key={ index }
        id={ item.id }
        name={ item.name }
        paramCodes={ item.paramCodes.join(', ') }
        params={ item.params }
        servicePurpose={ item.servicePurpose }
        flightId={ this.props.flightId }
      />);
    });

    return list;
  }

  buildBody() {
    if (this.props.pending !== false) {
      return <ContentLoader/>
    } else {
      return this.buildTemplatesList();
    }
  }

  render () {
    return (
      <div className='flight-templates-list container-fluid'>
        { this.buildBody() }
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    pending: state.flightTemplates.pending,
    flightTemplates: state.flightTemplates,
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    transmit: bindActionCreators(transmit, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(List);
