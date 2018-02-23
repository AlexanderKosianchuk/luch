import './item.sass';

import React, { Component } from 'react';

export default class Item extends Component {
  static textInput = null;

  changeHandle(event) {
    this.props.changeValue({
      key: this.props.id,
      value: this.textInput.value || ''
    });
  }

  render() {
    return (
      <div className="settings-item row">
        <span className="settings-item__label">{ this.props.label }</span>
        <input type="text" className='form-control'
          id={ this.props.id }
          value={ this.props.value }
          onChange={ this.changeHandle.bind(this) }
          ref={(input) => { this.textInput = input; }} />
      </div>
    );
  }
}
