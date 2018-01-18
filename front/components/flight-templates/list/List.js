import './list.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Collapse } from 'react-collapse';

import ContentLoader from 'controls/content-loader/ContentLoader';
import Item from 'components/flight-templates/item/Item';

import request from 'actions/request';

class List extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOpened: false
    };
  }

  componentWillMount() {
    this.props.request(
      ['templates', 'getFlightTemplates'],
      'get',
      'FLIGHT_TEMPLATES',
      { flightId: this.props.flightId }
    );
  }

  buildTemplatesList() {
    let list = [];
    this.props.flightTemplates.items.forEach((item, index) => {
      list.push(<Item
        key={ index }
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
    request: bindActionCreators(request, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(List);
