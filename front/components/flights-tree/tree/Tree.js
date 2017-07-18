import './tree.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';
import SortableTree, { getTreeFromFlatData, toggleExpandedForAll } from 'react-sortable-tree';

import FlightTitle from 'components/flights-tree/flight-title/FlightTitle';
import FolderTitle from 'components/flights-tree/folder-title/FolderTitle';
import FlightControls from 'components/flights-tree/flight-controls/FlightControls';
import FolderControls from 'components/flights-tree/folder-controls/FolderControls';

import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import transmit from 'actions/transmit';

const MAX_DEPTH = 5;
const FLIGHT_TYPE = 'flight';
const FOLDER_TYPE = 'folder';
const TOP_CONTROLS_HEIGHT = 105;

class Tree extends Component {
    constructor(props) {
        super(props);

        if (props.list) {
            this.state = {
                treeData: getTreeFromFlatData({
                    flatData: this.prepareTreeData(props.list)
                })
            };
        }
    }

    componentWillReceiveProps(nextProps) {
        var middleware = (data) => data;

        if ((this.props.expanded !== nextProps.expanded)
            && (typeof nextProps.expanded === 'boolean')
        ) {
            middleware = (data) => {
                return toggleExpandedForAll({
                    treeData: data,
                    expanded: nextProps.expanded
                });
            }
        }

        this.setState({
            treeData: middleware(
                getTreeFromFlatData({
                    flatData: this.prepareTreeData(nextProps.list)
                })
            )
        });
    }

    componentDidMount() {
        this.resize();

        if (this.props.pending !== false) {
            this.props.request(
                ['folder', 'getFolders'],
                'FOLDERS',
                'get'
            );
            this.props.request(
                ['flights', 'getFlights'],
                'FLIGHTS',
                'get'
            );
            this.props.request(
                ['users', 'getUserSettings'],
                'USER_SETTINGS',
                'get'
            );
        } else {
            this.checkChosen();
            this.flightClickEventListenerAdd();
        }
    }

    checkChosen() {
        let rows = document.getElementsByClassName('flights-tree-tree__item');
        for (var ii = 0; ii < rows.length; ii++) {
            rows[ii].classList.remove('is-chosen');
        }

        let flights = document.getElementsByClassName('flights-tree-tree__flight');
        this.props.chosenFlights.forEach((chosenFlight) => {
            for (var ii = 0; ii < flights.length; ii++) {
                let flightRow = flights[ii];
                let title = flightRow.getElementsByClassName('flights-tree-flight-title');
                let flightId = parseInt(title[0].getAttribute('data-flight-id'));

                if (chosenFlight.id === flightId) {
                    flightRow.classList.add('is-chosen');
                }
            };
        });
    }

    componentWillUnmount() {
        function removeEventListenerByClass(className, event, fn) {
            var list = document.getElementsByClassName(className);
            for (var ii = 0, len = list.length; ii < len; ii++) {
                list[ii][event] = '';
            }
        }

        removeEventListenerByClass(
            'rst__rowContents',
            'onclick'
        );
    }

    componentDidUpdate() {
        this.resize();

        this.flightClickEventListenerAdd();

        let chosen = document.getElementsByClassName('is-chosen');
        if (this.props.chosenFlights.length !== chosen.length) {
            this.checkChosen();
        }
    }

    flightClickEventListenerAdd()
    {
        function addEventListenerByClass(className, event, fn) {
            var list = document.getElementsByClassName(className);
            for (var ii = 0, len = list.length; ii < len; ii++) {
                list[ii][event] = fn;
            }
        }

        addEventListenerByClass(
            'rst__rowContents',
            'onclick',
            this.handleItemClick.bind(this)
        );
    }

    handleItemClick(event) {
        let currentTarget = event.currentTarget;
        let target = event.target;

        function findAncestor (el, cls) {
            while ((el = el.parentElement) && !el.classList.contains(cls));
            return el;
        }

        if (target.classList.contains('flights-tree-flight-controls')
            || findAncestor(target, 'flights-tree-flight-controls')
        ) {
            return;
        }

        let flightRow = findAncestor(currentTarget, 'flights-tree-tree__flight');

        // not a flight. Maybe folder
        if (!flightRow) {
            return;
        }

        let title = flightRow.getElementsByClassName('flights-tree-flight-title');
        let flightId = parseInt(title[0].getAttribute('data-flight-id'));

        flightRow.classList.toggle('is-chosen');
        this.props.transmit(
            'FLIGHTS_CHOISE_TOGGLE',
            {
                id: flightId,
                checkstate: flightRow.classList.contains('is-chosen')
            }
        );
    }

    resize(event) {
        this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
    }

    prepareTreeData(flatData) {
        if (!Array.isArray(flatData)) {
            return [];
        }

        flatData.forEach((item) => {
            if (item.type === FLIGHT_TYPE) {
                item.title = <FlightTitle flight={ item }/>;
            } else if (item.type === FOLDER_TYPE) {
                item.title = <FolderTitle folderInfo={ item }/>;
            }
        });

        return flatData;
    }

    updateTreeData(treeData) {
        this.setState({ treeData });
    }

    moveNodeHandler({ node, treeIndex, path }) {
        let treeData = this.state.treeData;
        let id = node.id;
        let parent = { id: 0 }; // if not found than moved to root

        this.findParent(treeData, id, (found) => { parent = found });
        let data = { id: id, parentId: parent.id };

        if (node.type === FLIGHT_TYPE) {
            this.props.request(
                ['flights', 'changeFlightPath'],
                'FLIGHT_PATH',
                'put',
                data
            );
        } else if (node.type === FOLDER_TYPE) {
            this.props.request(
                ['folder', 'changeFolderPath'],
                'FOLDER_PATH',
                'put',
                data
            );
        }
    }

    expandHandler({ treeData, node, expanded }) {
        this.props.request(
            ['folder', 'toggleFolderExpanding'],
            'FOLDER_EXPANDING',
            'put',
            {
                id: node.id,
                expanded: expanded
            }
        );
    }

    findParent(treeData, id, save) {
        treeData.forEach((item) => {
            let itemId = item.id;
            let children = item.children || [];

            children.forEach((childItem) => {
                if (childItem.id === id) {
                    save(item)
                } else {
                    this.findParent(children, id, save);
                }
            });
        });
    }

    buildTree() {
        return (<SortableTree
            rowHeight={ 50 }
            scaffoldBlockPxWidth={ 40 }
            maxDepth={ MAX_DEPTH }
            treeData={ this.state.treeData }
            onChange={ this.updateTreeData.bind(this) }
            onMoveNode={ this.moveNodeHandler.bind(this) }
            onVisibilityToggle={ this.expandHandler.bind(this) }
            canDrop={({ nextParent }) => !nextParent || !nextParent.noChildren}
            isVirtualized={ false }
            generateNodeProps={
                rowInfo => {
                    if (rowInfo.node.type === FLIGHT_TYPE) {
                        return {
                            buttons: [ <FlightControls flight={ rowInfo.node }/> ],
                            className: 'flights-tree-tree__item flights-tree-tree__flight',
                        }
                    } else if (rowInfo.node.type === FOLDER_TYPE) {
                        return {
                            buttons: [ <FolderControls folderInfo={ rowInfo.node }/> ],
                            className: 'flights-tree-tree__item flights-tree-tree__folder',
                        }
                    }
                }
            }
       />);
    }

    buildBody() {
        if (this.props.pending !== false) {
            return <ContentLoader/>
        } else {
            return this.buildTree();
        }
    }

    render() {
        return (
            <div className='flights-tree-tree'
                ref={(container) => { this.container = container; }}
            >
                { this.buildBody() }
            </div>
        );
    }
}

function merge(flights, folders) {
    if (Array.isArray(flights) && Array.isArray(folders)) {
        return folders.concat(flights);
    } else if (!Array.isArray(flights) && Array.isArray(folders)) {
        return folders;
    } else if (Array.isArray(flights) && !Array.isArray(folders)) {
        return flights;
    } else {
        return [];
    }
}

function isPending(flightsPending, foldersPending, settingsPending) {
    return !((flightsPending === false)
        && (foldersPending === false)
        && (settingsPending === false)
    );
}

function mapStateToProps(state) {
    return {
        pending: isPending(state.flights.pending, state.folders.pending, state.settings.pending),
        list: merge(state.flights.items, state.folders.items),
        chosenFlights: state.flights.chosenItems,
        expanded: state.folders.expanded
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        transmit: bindActionCreators(transmit, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Tree);
