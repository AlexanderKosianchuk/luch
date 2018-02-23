import './toolbar-input.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';

export default class ToolbarInput extends Component{
  constructor(props) {
    super(props);
    this.textInput = null;

    this.state = {
      value: props.value || ''
    }
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      value: nextProps.value || ''
    });
  }

  handleClick(event) {
    let textInputValue = this.textInput.value;
    if ((textInputValue !== null)
      && (typeof textInputValue === 'string')
      && (textInputValue.length > 0)
    ) {
      this.props.handleSaveClick(event, textInputValue);
    }
  }

  handleChange() {
    this.setState({
      value: this.textInput.value
    })
  }

  render() {
    return (
      <ul className='toolbar-input nav navbar-nav navbar-right'>
        <li><a href='#' className='toolbar-input__name'>
          <input
            className='form-control'
            type='text'
            value={ this.state.value || '' }
            onChange={ this.handleChange.bind(this) }
            ref={(input) => { this.textInput = input; }}
          />
        </a></li>
        <li><a href='#' onClick={ this.handleClick.bind(this) }>
          <span
            className='glyphicon glyphicon-floppy-disk'
            aria-hidden='true'>
          </span>
        </a></li>
      </ul>
    );
  }
}

ToolbarInput.propTypes = {
  handleSaveClick: PropTypes.func.isRequired,
  value:  PropTypes.string,
};
