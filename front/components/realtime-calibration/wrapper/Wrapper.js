import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import VerticalToolbar from 'components/realtime-calibration/vertical-toolbar/VerticalToolbar';
import DataContainer from 'components/realtime-calibration/data-container/DataContainer';
import uuidV4 from 'uuid/v4';

import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import redirect from 'actions/redirect';

const UID = uuidV4().substring(0, 18).replace(/-/g, '');

class Wrapper extends Component {
  componentDidMount() {
    if (this.props.fdrsFetching === null) {
      this.props.request(
        ['fdr', 'getFdrs'],
        'get',
        'FDRS'
      ).then(() => {
        if (this.props.fdrId !== this.props.chosen.id) {
          this.props.redirect('/realtime-calibration/fdr-id/' + this.props.chosen.id)
        }
      });
    } else {
      if (this.props.fdrId !== this.props.chosen.id) {
        this.props.redirect('/realtime-calibration/fdr-id/' + this.props.chosen.id)
      }
    }
  }

  componentWillReceiveProps(newProps) {
    if (newProps.chosen.id
        && (this.props.fdrId !== newProps.chosen.id)
    ) {
      this.props.redirect('/realtime-calibration/fdr-id/' + newProps.chosen.id)
    }
  }

  buildBody() {
    if (this.props.fdrsFetching !== false) {
      return <ContentLoader/>
    } else {
      return (
        <div><div className='col-sm-3'>
          <VerticalToolbar
            uid={ UID }
            fdrId={ this.props.chosen.id }
          />
        </div>
        <div className='col-sm-9'>
          <DataContainer
            uid={ UID }
            fdrId={ this.props.chosen.id }
          />
        </div></div>
      );
    }
  }

  render() {
    return (
      <div className='row'>
        { this.buildBody() }
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    fdrsFetching: state.fdrs.pending,
    chosen: state.fdrs.chosen,
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    redirect: bindActionCreators(redirect, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Wrapper);
