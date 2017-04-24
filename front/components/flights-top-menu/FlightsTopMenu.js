import './flights-top-menu.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import FlightUploaderDropdown from 'components/flight-uploader-dropdown/FlightUploaderDropdown';
import FlightImporterDropdown from 'components/flight-importer-dropdown/FlightImporterDropdown';
import FlightUploadingProgressIndicator from 'components/flight-uploading-progress-indicator/FlightUploadingProgressIndicator';

export default class FlightsTopMenu extends React.Component {
    logout() {
        this.props.topMenuService.userLogout();
    }

    showOptions() {
        this.props.topMenuService.userOptionsShow();
    }

    changeLanguage(event) {
        let language = event.target.getAttribute("data-lang");
        this.props.topMenuService.changeLanguage(language);
    }

    buildLanguageMenu() {
        return this.props.avaliableLanguages.map(item => {
            if (item.toUpperCase() !== this.props.userLang.toUpperCase()) {
                return <li key={ item }><a href="#" onClick={ this.changeLanguage.bind(this) } data-lang={ item }>{ item }</a></li>
            }
        });
    }

    render() {
        this.languageMenu = this.buildLanguageMenu();
        return (
            <nav className="flights-top-menu navbar navbar-dark">
              <div className="container-fluid">
                <div className="navbar-header">
                    <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-menu-navbar-collapse" aria-expanded="false">
                        <span className="sr-only">Toggle navigation</span>
                        <span className="flights-top-menu__icon-bar icon-bar"></span>
                        <span className="flights-top-menu__icon-bar icon-bar"></span>
                        <span className="flights-top-menu__icon-bar icon-bar"></span>
                      </button>
                    <a className="main-menu-toggle navbar-brand is-hoverable" href="#">
                        <span className="main-menu-toggle flights-top-menu__main-menu-toggle glyphicon glyphicon-menu-hamburger"></span>
                    </a>
                    <a className="flights-top-menu__navbar-brand navbar-brand" href="#">Luch</a>
                </div>
                <div className="collapse navbar-collapse" id="top-menu-navbar-collapse">
                  <ul className="nav navbar-nav">
                    <li className="dropdown">
                      <a href="#" className="flight-importer-dropdown-toggle dropdown-toggle is-hoverable" role="button">
                        { this.props.i18n.fileImport }
                      </a>
                      <FlightImporterDropdown />
                    </li>
                  </ul>

                  <ul className="nav navbar-nav">
                    <li className="dropdown">
                      <a href="#" className="flight-uploader-dropdown-toggle dropdown-toggle is-hoverable" role="button">
                        { this.props.i18n.flightUploaderUpload }
                      </a>
                      <FlightUploaderDropdown
                          i18n={ this.props.i18n }
                          topMenuService={ this.props.topMenuService }
                      />
                    </li>
                  </ul>

                  <ul className="nav navbar-nav">
                      <FlightUploadingProgressIndicator />
                  </ul>

                  <ul className="nav navbar-nav navbar-right">
                    <li><span>{ this.props.userLogin }</span></li>
                    <li className="dropdown">
                      <a href="#" className="dropdown-toggle is-hoverable" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        { this.props.userLang.toUpperCase() }
                     </a>
                      <ul className="flights-top-menu__dropdown-menu flights-top-menu__language-menu dropdown-menu">
                        { this.languageMenu }
                      </ul>
                    </li>
                    <li><a className="is-hoverable" onClick={ this.showOptions.bind(this) } href="#">
                        <span className="glyphicon glyphicon-cog"></span>
                    </a></li>
                    <li><a className="is-hoverable" onClick={ this.logout.bind(this) } href="#">
                        <span className="glyphicon glyphicon-log-out"></span>
                    </a></li>
                  </ul>
                </div>
              </div>
            </nav>
        );
    }
}
