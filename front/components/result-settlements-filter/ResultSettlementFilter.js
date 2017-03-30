import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import changeSettlementItemCheckstateAction from 'actions/changeSettlementItemCheckstate';
import applyResultSettlementFilterAction from 'actions/applyResultSettlementFilter';
import SettlementsFilterItem from 'components/settlements-filter-item/SettlementsFilterItem';

class ResultSettlementFilter extends React.Component {
    constructor(props) {
        super(props);
        this.settlementItems = [];
    }

    handleSubmit(event) {
        alert('A name was submitted: ');
        event.preventDefault();
    }

    componentWillReceiveProps(nextProps) {
        if (!nextProps.hasOwnProperty('avaliableSettlements')) {
            return;
        }

        let settlements = nextProps.avaliableSettlements;

        this.settlementItems = settlements.map((settlement) => {
            let label = settlement.text;
            if (this.props.i18n[settlement.text]) {
                label = this.props.i18n[settlement.text];
            }

            return (
                <SettlementsFilterItem
                    key={settlement.id}
                    id={settlement.id}
                    label={label}
                    changeCheckstate={this.props.changeCheckstate}
                />
            );
        });

        this.setState(nextProps);
    }

    shouldComponentUpdate(nextProps, nextState) {
        if (JSON.stringify(this.props.avaliableSettlements)
            === JSON.stringify(nextProps.avaliableSettlements)
        ) {
            return false;
        }

        return true;
    }

    render() {
        let body = '';
        let button ='';
        if (this.settlementItems.length !== 0) {
            body = this.settlementItems;
            button = <div className="form-group">
                <input type="submit" className="btn btn-default" value="Apply" />
            </div>;
        }

        return (
            <form onSubmit={this.handleSubmit.bind(this)}>
                { body }
                { button }
            </form>
        );
    }
}

function mapStateToProps (state) {
    return { ...state.settlementFilter };
}

function mapDispatchToProps(dispatch) {
    return {
        changeCheckstate: bindActionCreators(changeSettlementItemCheckstateAction, dispatch),
        applyResultSettlementFilter: bindActionCreators(applyResultSettlementFilterAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ResultSettlementFilter);
