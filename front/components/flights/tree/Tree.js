import './tree.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';
import SortableTree, { getTreeFromFlatData, getNodeAtPath } from 'react-sortable-tree';

import FlightTitle from 'components/flights/flight-title/FlightTitle';
import FolderTitle from 'components/flights/folder-title/FolderTitle';
import FlightControls from 'components/flights/flight-controls/FlightControls';
import FolderControls from 'components/flights/folder-controls/FolderControls';

import ContentLoader from 'controls/content-loader/ContentLoader';

import getFlightsList from 'actions/getFlightsList';
import getFoldersList from 'actions/getFoldersList';
import getSettings from 'actions/getSettings';
import moveFlight from 'actions/moveFlight';
import moveFolder from 'actions/moveFolder';
import toggleFolderExpanding from 'actions/toggleFolderExpanding';

const MAX_DEPTH = 5;
const FLIGHT_TYPE = 'flight';
const FOLDER_TYPE = 'folder';
const TOP_CONTROLS_HEIGHT = 105;

class Tree extends Component {
    constructor(props) {
        super(props);

        if (props.list
            && (props.list.length > 0)
        ) {
            this.state = {
                treeData: getTreeFromFlatData({
                    flatData: this.prepareTreeData(props.list)
                })
            };
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            treeData: this.getTreeData(nextProps.list)
        });
    }

    getTreeData(list) {
        return getTreeFromFlatData({
            flatData: this.prepareTreeData(list)
        });
    }

    componentDidMount() {
        this.resize();

        if (this.props.pending !== false) {
            this.props.getFlightsList();
            this.props.getFoldersList();
            this.props.getSettings();
        }
    }

    componentDidUpdate() {
        this.resize();
    }

    resize() {
        this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
    }

    prepareTreeData(flatData) {
        if (!Array.isArray(flatData)) {
            return [];
        }

        flatData.forEach((item) => {
            if (item.type === FLIGHT_TYPE) {
                item.title = <FlightTitle flightInfo={ item }/>;
            } else if (item.type === FOLDER_TYPE) {
                item.title = <FolderTitle folderInfo={ item }/>;
            }
        });

        return flatData;
    }

    updateTreeData (treeData) {
        this.setState({ treeData });
    }

    moveNodeHandler ({ node, treeIndex, path }) {
        let treeData = this.state.treeData;
        let id = node.id;
        let parent = { id: 0 }; // if not found than moved to root

        this.findParent(treeData, id, (found) => { parent = found });
        let data = { id: id, parentId: parent.id };

        if (node.type === FLIGHT_TYPE) {
            this.props.moveFlight(data);
        } else if (node.type === FOLDER_TYPE) {
            this.props.moveFolder(data);
        }
    }

    expandHandler({ treeData, node, expanded }) {
        this.props.toggleFolderExpanding({
            id: node.id,
            expanded: expanded
        });
    }

    findParent (treeData, id, save) {
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

    buildTree () {
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
                            buttons: [ <FlightControls flightInfo={ rowInfo.node }/> ],
                            className: 'flights-tree__flight'
                        }
                    } else if (rowInfo.node.type === FOLDER_TYPE) {
                        return {
                            buttons: [ <FolderControls folderInfo={ rowInfo.node }/> ],
                            className: 'flights-tree__folder'
                        }
                    }
                }
            }
       />);
    }

    buildBody () {
        if (this.props.pending !== false) {
            return <ContentLoader/>
        } else {
            return this.buildTree();
        }
    }

    render () {
        return (
            <div className='flights-tree'
                ref={(container) => { this.container = container; }}
            >
                { this.buildBody() }
            </div>
        );
    }
}

function merge (flightsListItems, foldersListItems) {
    if (Array.isArray(flightsListItems) && Array.isArray(foldersListItems)) {
        return foldersListItems.concat(flightsListItems);
    } else if (!Array.isArray(flightsListItems) && Array.isArray(foldersListItems)) {
        return foldersListItems;
    } else if (Array.isArray(flightsListItems) && !Array.isArray(foldersListItems)) {
        return flightsListItems;
    } else {
        return [];
    }
}

function isPending(flightsListPending, foldersListPending, settingsPending) {
    return !((flightsListPending === false)
        && (foldersListPending === false)
        && (settingsPending === false)
    );
}

function mapStateToProps (state) {
    return {
        pending: isPending(state.flightsList.pending, state.foldersList.pending, state.settings.pending),
        list: merge(state.flightsList.items, state.foldersList.items)
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightsList: bindActionCreators(getFlightsList, dispatch),
        getFoldersList: bindActionCreators(getFoldersList, dispatch),
        getSettings: bindActionCreators(getSettings, dispatch),
        moveFlight: bindActionCreators(moveFlight, dispatch),
        moveFolder: bindActionCreators(moveFolder, dispatch),
        toggleFolderExpanding: bindActionCreators(toggleFolderExpanding, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Tree);
