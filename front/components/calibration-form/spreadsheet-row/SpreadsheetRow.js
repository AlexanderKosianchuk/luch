import './spreadsheet-row.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';

export default class Spreadsheet extends Component {
    constructor(props) {
        super(props);

        this.state = {
            x: props.x,
            y: props.y
        }
    }

    handleChange(attr, event) {
        this.setState({
            [attr]: parseInt(event.target.value)
        });
    }

    handleClick(event) {
        event.preventDefault();
        event.stopPropagation();

        this.el.remove();
    }

    render() {
        return (
            <div className='calibration-form-spreadsheet-row'
                ref={ (el) => { this.el = el }}
            >
                <input className='form-control calibration-form-spreadsheet-row__input'
                    value={ this.state.x }
                    onChange={ this.handleChange.bind(this, 'x') }
                />
                <input className='form-control calibration-form-spreadsheet-row__input'
                    value={ this.state.y }
                    onChange={ this.handleChange.bind(this, 'y') }
                />

                <button
                    className='btn btn-danger calibration-form-spreadsheet-row__button'
                    onClick={ this.handleClick.bind(this) }
                >
                    <span className='glyphicon glyphicon-trash'></span>
                </button>
            </div>
        );
    }
}

Spreadsheet.propTypes = {
    x: PropTypes.number.isRequired,
    y: PropTypes.number.isRequired
};
