import './checkbox.sass'

import React from 'react';
import Guid from 'guid';

export default function Checkbox (props) {
    const uid = Guid.create();
    return (
        <section className='checkbox'>
            <div className='checkbox__container'>
                <input id={ 'checkbox__input-' + uid }
                  type='checkbox'
                  value=''
                  name='check'
                  checked={ props.checkstate || false }
                  onChange={ props.changeCheckState || (() => {}) }
                />
                <label htmlFor={ 'checkbox__input-' + uid }></label>
            </div>
        </section>
    );
}
