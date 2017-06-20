import './color-picker.sass'

import React from 'react';
import { I18n } from 'react-redux-i18n';
import { SketchPicker } from 'react-color';

export default function ColorPicker(props) {
    let color = props.color;

    function handleChangeComplete(event) {
        color = event.hex;
    }

    function handleClick() {
        props.applyColor(color);
    }

    function render() {
        if (props.isEnabled === false) {
            return null;
        }

        if (!props.isShown) {
            return null;
        }

        return <div className='cyclo-params-color-picker'>
            <SketchPicker
                color={ color }
                onChangeComplete={ handleChangeComplete }
            />
            <div className='cyclo-params-color-picker__controls'>
                <button
                    className='cyclo-params-color-picker__button btn btn-default pull-left'
                    onClick={ props.toggleColorPicker }
                >
                    { I18n.t('cycloParams.colorPicker.cancel') }
                </button>
                <button
                    className='cyclo-params-color-picker__button btn btn-default pull-right'
                    onClick={ handleClick }
                >
                    { I18n.t('cycloParams.colorPicker.save') }
                </button>
            </div>
        </div>;
    }

    return render();
}
