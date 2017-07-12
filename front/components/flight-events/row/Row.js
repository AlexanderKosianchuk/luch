import './row.sass'

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';

import Checkbox from 'controls/checkbox/Checkbox';

import changeFlightEventReliability from 'actions/particular/changeFlightEventReliability';

const columns = [
    { attribute: 'start', style: 'col-sm-1' },
    { attribute: 'end', style: 'col-sm-1' },
    { attribute: 'duration', style: 'col-sm-1' },
    { attribute: 'code', style: 'col-sm-1' },
    { attribute: 'comment', style: 'col-sm-2' },
    { attribute: 'algText', style: 'col-sm-1' },
    { attribute: 'excAditionalInfo', style: 'col-sm-2' },
    { attribute: 'reliability', style: 'col-sm-1' },
    { attribute: 'userComment', style: 'col-sm-2' },
];

const columnsShort = [
    { attribute: 'sample', style: 'col-sm-2' },
    { attribute: 'duration', style: 'col-sm-2' },
    { attribute: 'code', style: 'col-sm-2' },
    { attribute: 'comment', style: 'col-sm-3' },
    { attribute: 'excAditionalInfo', style: 'col-sm-3' },
];

const coloredStatuses = ['c', 'd', 'e'];

class Row extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isChecked: props.item.reliability ? 'checked' : ''
        }

        this.isShort = props.isShort || false
    }

    handleClick(eventId, eventType) {
        let boolValue = (this.state.isChecked === '');
        this.setState({
            isChecked: boolValue ? 'checked' : ''
        });

        this.props.changeReliability({
            flightId: this.props.flightId,
            eventId: eventId,
            eventType: eventType,
            reliability: boolValue
        });
    }

    buildCheckbox(item) {
        let eventId = item.id;
        let eventType = item.eventType;

        return <Checkbox
            checkstate={ this.state.isChecked }
            changeCheckState={ this.handleClick.bind(this, eventId, eventType) }
        />
    }

    buildCellContent(item, attribute) {
        if (attribute === 'reliability') {
            return this.buildCheckbox(item);
        }

        if (attribute === 'sample') {
            return <div>
                <div>{ item.start } </div>
                <div>{ item.end } </div>
            </div>
        }

        return <span>{ item[attribute] }</span>
    }

    buildCells(item) {
        let columnsToBuild = this.isShort ? columnsShort : columns;
        return columnsToBuild.map((col, colIndex) => {
            return (
                <div key={ colIndex } className={ 'flight-events-row__cell ' + col.style }>
                    { this.buildCellContent(item, col.attribute) }
                </div>
            );
        })
    }

    getStatusClass(status) {
        status = status.toLowerCase();
        if (coloredStatuses.indexOf(status) >= 0) {
            return 'flight-events-row-' + status;
        }

        return '';
    }

    render() {
        return (
            <div
                className={
                    'flight-events-row row '
                    + this.getStatusClass(this.props.item.status)
                }
            >
                { this.buildCells(this.props.item) }
            </div>
        );
    }
}

Row.propTypes = {
    flightId: PropTypes.number.isRequired,
    item: PropTypes.object.isRequired,
    columns: PropTypes.array,
    isShort:  PropTypes.bool
};

function mapStateToProps() {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        changeReliability: bindActionCreators(changeFlightEventReliability, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(Row);
