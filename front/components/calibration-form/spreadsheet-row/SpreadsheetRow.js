import './spreadsheet-row.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';

export default class SpreadsheetRow extends Component {
    constructor(props) {
        super(props);

        this.update = (typeof props.update === 'function')
            ? props.update
            : () => {};

        this.state = {
            x: props.x,
            y: props.y
        }
    }

    componentWillReceiveProps(newProps) {
        this.setState({
            x: newProps.x,
            y: newProps.y
        });
    }

    handleChange(attr, event) {
        let val = parseInt(event.target.value);

        if (isNaN(val)) {
            val = -1;
        }

        this.setState({
            [attr]: val
        });
    }

    handleClick(index, event) {
        event.preventDefault();
        event.stopPropagation();

        this.update('xy', index, null);
    }

    handleBlur(attr, index) {
        this.update(attr, index, this.state[attr]);
    }

    handleKeyPress(attr, index, event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            event.stopPropagation();

            this.update(attr, index, this.state[attr]);
        }
    }

    render() {
        return (
            <div className='calibration-form-spreadsheet-row'
                ref={ (el) => { this.el = el }}
            >
                <input className='form-control calibration-form-spreadsheet-row__input'
                    name={ 'Param[' + this.props.paramId + '][y]' }
                    value={ this.state.y }
                    onChange={ this.handleChange.bind(this, 'y') }
                    onBlur={ this.handleBlur.bind(this, 'y', this.props.index) }
                    onKeyPress={ this.handleKeyPress.bind(this, 'y', this.props.index) }
                />
                <input className='form-control calibration-form-spreadsheet-row__input'
                    name={ 'Param[' + this.props.paramId + '][x]' }
                    value={ this.state.x }
                    onChange={ this.handleChange.bind(this, 'x') }
                    onBlur={ this.handleBlur.bind(this, 'x', this.props.index) }
                    onKeyPress={ this.handleKeyPress.bind(this, 'x', this.props.index) }
                />

                <button
                    className='btn btn-danger calibration-form-spreadsheet-row__button'
                    onClick={ this.handleClick.bind(this, this.props.index) }
                >
                    <span className='glyphicon glyphicon-trash'></span>
                </button>
            </div>
        );
    }
}

SpreadsheetRow.propTypes = {
    paramId: PropTypes.number.isRequired,
    x: PropTypes.number.isRequired,
    y: PropTypes.number.isRequired,
    update: PropTypes.func
};
