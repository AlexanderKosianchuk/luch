import './flights-top-menu.sass';

import React from 'react';

export default function FlightsTopMenu (props) {
    return (
        <div className="flights-top-menu fluid-grid">
            <div className="row">
                <div className="col-sm-1">
                    <span className="flights-top-menu__btn is-hoverable" onClick={ props.showMenu }>
                        <span id="main-menu-toggle" className="flights-top-menu__gliphicon flights-top-menu__gliphicon-menu glyphicon glyphicon-menu-hamburger"></span>
                        <span className="flights-top-menu__label is-hidden">Menu</span>
                    </span>
                </div>
                <div className="flights-top-menu__col-no-padding col-sm-1">
                    <span className="flights-top-menu__btn">
                        <span className="flights-top-menu__label">Luch</span>
                    </span>
                </div>
                <div className="col-sm-3">
                    <span className="flights-top-menu__btn is-hoverable">
                        <span className="flights-top-menu__gliphicon glyphicon glyphicon-upload"></span>
                        <span className="flights-top-menu__label">{ props.i18n.flightUploaderUpload }</span>
                    </span>
                </div>
                <div className="col-sm-5">
                    <span className="flights-top-menu__btn is-aligned-right">
                        <span className="flights-top-menu__label">{ props.userLogin }</span>
                    </span>
                </div>
                <div className="col-sm-1">
                    <span className="flights-top-menu__btn is-hoverable">
                        <span className="flights-top-menu__gliphicon flights-top-menu__gliphicon-config glyphicon glyphicon-cog"></span>
                        <span className="flights-top-menu__label is-hidden">{ props.i18n.options }</span>
                    </span>
                </div>
                <div className="col-sm-1">
                    <span className="flights-top-menu__btn is-hoverable">
                        <span className="flights-top-menu__gliphicon flights-top-menu__gliphicon-logout glyphicon glyphicon-log-out"></span>
                        <span className="flights-top-menu__label is-hidden">{ props.i18n.exit }</span>
                    </span>
                </div>
            </div>
        </div>
    );

}
