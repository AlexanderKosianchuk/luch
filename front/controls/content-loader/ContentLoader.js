import './content-loader.sass';

import React from 'react';

export default function ContentLoader (props) {
    function getMargin() {
        if (props.margin) {
            return { margin: props.margin + 'px'};
        }

        return {};
    }

    function getStyle() {
        let style = {};

        if (props.size) {
            style = { ...style, ...{
                width: props.size + 'px',
                height: props.size + 'px'
            }};
        }

        if (props.border) {
            style = { ...style, ...{
                borderWidth: props.border + 'px',
                borderTopWidth: props.border + 'px'
            }};
        }

        return style;
    }

    return (
        <div className={ "content-loader row " + (props.className || '') }
            style={ getMargin() }
        >
            <div className="content-loader__loading"
                style={ getStyle() }
            ></div>
        </div>
    );
}
