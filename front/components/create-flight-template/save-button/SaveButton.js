import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import redirect from 'actions/redirect';

class CreateButton extends React.Component {
    handleClick()
    {
        this.props.redirect('/flight-templates/'
            + 'flight-id/'+ this.props.flightId + '/'
            + 'fdr-id/'+ this.props.fdrId + '/'
            + 'create'
        );
    }

    render() {
        return <ul className="nav navbar-nav navbar-right">
          <li><a href="#" onClick={ this.handleClick.bind(this) }>
              <span
                  className="glyphicon glyphicon-floppy-disk"
                  aria-hidden="true">
              </span>
          </a></li>
        </ul>;
    }
}

function mapStateToProps (state) {
    return {
        fdrId: state.flightInfo.fdrId
    }
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(CreateButton);
