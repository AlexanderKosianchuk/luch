import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import redirect from 'actions/redirect';

class FlightListViewSwitch extends React.Component {
  handleChangeView(event) {
    let viewState = event.target.getAttribute("data");

    switch(viewState) {
      case "tree":
        this.props.redirect('/flights/tree');
        break;
      case "table":
        this.props.redirect('/flights/table');
        break;
    }
  }

  render() {
    return (
      <ul className="nav navbar-nav">
        <li className={ this.props.viewType !== 'table' ? 'active' : '' } onClick={ this.handleChangeView.bind(this) }>
          <a data="tree" href="#">{ I18n.t('flightListViewSwitch.treeView') }</a>
        </li>
        <li className={ this.props.viewType === 'table' ? 'active' : '' } onClick={ this.handleChangeView.bind(this) }>
          <a data="table" href="#">{ I18n.t('flightListViewSwitch.tableView') }</a>
        </li>
      </ul>
    );
  }
}

function mapDispatchToProps(dispatch) {
  return {
    redirect: bindActionCreators(redirect, dispatch)
  }
}

export default connect(() => { return {} }, mapDispatchToProps)(FlightListViewSwitch);
