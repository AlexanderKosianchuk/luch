import './color-picker.sass'

import React from 'react';
import { I18n } from 'react-redux-i18n';
import { SketchPicker } from 'react-color';

export default function ColorPicker(props) {
    function render() {
        if (props.isShown) {
            return <div className='cyclo-params-item-color-picker'>
                <SketchPicker color={ props.color }/>
                <div className='cyclo-params-item-color-picker__controls'>
                    <button
                        onClick={ props.toggleColorPicker }
                        className='cyclo-params-item-color-picker__button btn btn-default pull-left'
                    >
                        { I18n.t('cycloParams.colorPicker.cancel') }
                    </button>
                    <button className='cyclo-params-item-color-picker__button btn btn-default pull-right'>
                        { I18n.t('cycloParams.colorPicker.save') }
                    </button>
                </div>
            </div>;
        } else {
            return null;
        }
    }

    return render();
}
