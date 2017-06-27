import './menu-dropdown.sass';

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';

import ContentLoader from 'controls/content-loader/ContentLoader';

import folderListExpandingToggle from 'actions/folderListExpandingToggle';
import flightListUnchooseAll from 'actions/flightListUnchooseAll';
import deleteFlight from 'actions/deleteFlight';
import exportFlight from 'actions/exportFlight';
import exportFlightCoordinates from 'actions/exportFlightCoordinates';
import processFlight from 'actions/processFlight';
import redirect from 'actions/redirect';

class MenuDropdown extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isLoaderShown: false
        };
    }

    buildMenuItems(type) {
        const menuItems = {
            oneFlight: [
                'delete',
                'export',
                'process',
                'exportCoordinates',
                'removeSelection',
                'events',
                'params',
                'templates'
            ],
            manyItems: [
                'export',
                'delete',
                'removeSelection'
            ],
        };

        let currentMenuItems = menuItems[type];

        function ucFirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        return currentMenuItems.map(item => {
            if (typeof this['handle' + ucFirst(item)] === 'function') {
                return <li key={ item } >
                    <a onClick={ this['handle' + ucFirst(item)].bind(this) }
                        href='#'>{ I18n.t('flightsTable.menuDropdown.'+item) }
                    </a>
                </li>;
            }
        });
    }

    buildMenu() {
        let flightsCount = this.props.flightsList.chosenItems.length;

        if (flightsCount === 0) {
            return '';
        }

        let type = 'manyItems';
        if (flightsCount === 1) {
            type = 'oneFlight';
        }

        return(
            <li className='dropdown'>
                <a href='#' className='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>
                    <Translate value='flightsTable.menuDropdown.fileMenu' /><span className='caret'></span>
                </a>
                <ul className='dropdown-menu'>
                    { this.buildMenuItems(type) }
                </ul>
            </li>
        );
    }

    handleDelete() {
        for (var ii = 0; ii < this.props.flightsList.chosenItems.length; ii++) {
            let item = this.props.flightsList.chosenItems[ii];
            this.props.deleteFlight({ id: item.id});
        }
    }

    handleExport() {
        let items = [];

        for (var ii = 0; ii < this.props.flightsList.chosenItems.length; ii++) {
            items.push(this.props.flightsList.chosenItems[ii].id);
        }

        this.props.exportFlight(items);
    }

    handleProcess() {
        if (this.props.flightsList.chosenItems.length === 1) {
            this.setState({ isLoaderShown: true });
            this.props.processFlight({
                id: this.props.flightsList.chosenItems[0].id
            }).then(() => {
                this.setState({ isLoaderShown: false });
            });
        }
    }

    handleExportCoordinates() {
        if (this.props.flightsList.chosenItems.length === 1) {
            this.props.exportFlightCoordinates({
                id: this.props.flightsList.chosenItems[0].id
            });
        }
    }

    handleRemoveSelection() {
        this.props.flightListUnchooseAll();
    }

    handleEvents() {
        if (this.props.flightsList.chosenItems.length === 1) {
            this.props.redirect('/flight-events/' + this.props.flightsList.chosenItems[0].id);
        }
    }

    handleParams() {
        if (this.props.flightsList.chosenItems.length === 1) {
            this.props.redirect('/flight-params/' + this.props.flightsList.chosenItems[0].id);
        }
    }

    handleTemplates() {
        if (this.props.flightsList.chosenItems.length === 1) {
            this.props.redirect('/flight-templates/' + this.props.flightsList.chosenItems[0].id);
        }
    }

    buildLoader() {
        if (this.state.isLoaderShown) {
            return (<li>
                <a href='#' className='flights-tree-menu-dropdown__loader'>
                    <ContentLoader margin={ 1 } size={ 30 } border= { 3 } />
                </a>
            </li>);
        }

        return '';
    }

    render() {
        return (
            <ul className='flights-tree-menu-dropdown nav navbar-nav'>
                { this.buildMenu() }
                { this.buildLoader() }
            </ul>
        );
    }
}

function mapStateToProps (state) {
    return {
         flightsList: state.flightsList
     };
}

function mapDispatchToProps(dispatch) {
    return {
        folderListExpandingToggle: bindActionCreators(folderListExpandingToggle, dispatch),
        flightListUnchooseAll: bindActionCreators(flightListUnchooseAll, dispatch),
        deleteFlight: bindActionCreators(deleteFlight, dispatch),
        exportFlight: bindActionCreators(exportFlight, dispatch),
        exportFlightCoordinates: bindActionCreators(exportFlightCoordinates, dispatch),
        processFlight: bindActionCreators(processFlight, dispatch),
        redirect: bindActionCreators(redirect, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(MenuDropdown);
