import React from 'react';

export default class FlightListTypeDropdown extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            treeActive: "active",
            tableActive: ""
        }
    }

    handleChangeView(event) {
        let viewState = event.target.getAttribute("data");

        switch(viewState) {
            case "tree":
                this.props.flightViewService.showTree();
                this.setState({
                    treeActive: "active",
                    tableActive: ""
                });
                break;
            case "table":
                this.props.flightViewService.showTable();
                this.setState({
                    treeActive: "",
                    tableActive: "active"
                });
                break;
        }
    }

    render() {
        return (
            <ul className="nav navbar-nav">
                <li className={ this.state.treeActive } onClick={ this.handleChangeView.bind(this) }>
                    <a data="tree" href="#">{ this.props.i18n.treeView }</a>
                </li>
                <li className={ this.state.tableActive } onClick={ this.handleChangeView.bind(this) }>
                    <a data="table" href="#">{ this.props.i18n.tableView }</a>
                </li>
            </ul>
        );
    }
}
