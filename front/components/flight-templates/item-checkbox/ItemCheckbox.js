import './item-checkbox.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import transmit from 'actions/transmit';

class ItemCheckbox extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      checkstate: props.checkstate || ''
    };
  }

  componentDidMount() {
    if (this.state.checkstate === 'checked') {
      this.props.transmit(
        'TEMPLATE_CHOSEN',
        { id: this.props.id }
      );
    }
  }

  changeCheckState() {
    let newCheckstate = 'checked';
    if (this.state.checkstate === 'checked') {
      newCheckstate = '';
      this.props.transmit(
        'TEMPLATE_UNCHOSEN',
        { id: this.props.id }
      );
    } else {
      this.props.transmit(
        'TEMPLATE_CHOSEN',
        { id: this.props.id }
      );
    }

    this.setState({ checkstate: newCheckstate });
  }

  render () {
    return (
      <section className='flight-templates-item-checkbox'>
        <div className='flight-templates-item-checkbox__container'>
          <input id={ 'flight-templates-item-checkbox_' + this.props.id }
            type='checkbox'
            value='None'
            name='check'
            checked={ this.state.checkstate }
            onChange={ this.changeCheckState.bind(this) }
           />
          <label htmlFor={ 'flight-templates-item-checkbox_' + this.props.id }></label>
        </div>
        </section>
    );
  }
}

function mapStateToProps(state) {
  return {};
}

function mapDispatchToProps(dispatch) {
  return {
    transmit: bindActionCreators(transmit, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ItemCheckbox);
