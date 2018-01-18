import React from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';
import FlightTemplateEditToolbar from 'controls/flight-template-edit-toolbar/FlightTemplateEditToolbar';
import CycloParams from 'controls/cyclo-params/CycloParams';

class FlightTemplateCreate extends React.Component {
  render () {
    return (
      <div>
        <Menu/>
        <FlightTemplateEditToolbar
          flightId={ this.props.flightId }
        />
        <CycloParams
          flightId={ this.props.flightId }
          colorPickerEnabled={ false }
        />
      </div>
    );
  }
}

function mapStateToProps(state, ownProps) {
  return {
    flightId: ownProps.match.params.flightId
  };
}

export default connect(mapStateToProps, () => { return{} })(FlightTemplateCreate);
