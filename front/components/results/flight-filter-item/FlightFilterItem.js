import React from 'react';

export default function FlightFilterItem (props) {
  let textInput = null;

  function changeItem() {
    props.changeFlightFilterItem({
      [props.propName]: textInput.value
    });
  }

  return (
    <div className="form-group">
      <label htmlFor={props.id}>{props.label}</label>
      <input type="text" className="form-control"
        id={props.id} placeholder={props.placeholder}
        onBlur={changeItem}
        ref={(input) => { textInput = input; }} />
    </div>
  );

}
