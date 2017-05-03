import './content-loader.sass';

import React from 'react';

export default function  ContentLoader (props) {
    function getMargin() {
        if (props.margin) {
            return { margin: props.margin + 'px'};
        }

        return '';
    }

    function getSize()
    {
        if (props.size) {
            return {
                width: props.size + 'px',
                height: props.size + 'px'
            };
        }

        return '';
    }

    return (
        <div className="content-loader row"
            style={ getMargin() }
        >
            <div className="content-loader__loading"
                style={ getSize() }
            ></div>
        </div>
    );
}
