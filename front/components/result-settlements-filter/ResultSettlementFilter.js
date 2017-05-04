import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import changeSettlementItemCheckstateAction from 'actions/changeSettlementItemCheckstate';
import applySettlementFilterAction from 'actions/applySettlementFilter';
import SettlementsFilterItem from 'components/settlements-filter-item/SettlementsFilterItem';
import ContentLoader from 'components/content-loader/ContentLoader';

class ResultSettlementFilter extends React.Component {
    constructor(props) {
        super(props);
    }

    handleSubmit(event) {
        let settlementFilter  = this.props.settlementFilter;
        let flightFilter = this.props.flightFilter;

        if (settlementFilter
            && !this.allEmpty(this.props.flightFilter)
            && Array.isArray(settlementFilter.chosenSettlements)
            && (settlementFilter.chosenSettlements.length > 0)
        ) {
            let chosenSettlements = settlementFilter.chosenSettlements.map((item) => item.id);
            this.props.applySettlementFilter({
                chosenSettlements: chosenSettlements,
                flightFilter: flightFilter
            });
        }
        event.preventDefault();
    }

    buildSettlements(settlements) {
        return settlements.map((settlement) => {
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
    }

    allEmpty (obj) {
        for (var key in obj) {
            if (obj[key] !== null && obj[key] != "")
                return false;
        }
        return true;
    }

    render() {
        let body = this.props.i18n.putFlightFilter;
        let button ='';
        let settlementFilter  = this.props.settlementFilter;

        if (!this.allEmpty(this.props.flightFilter)
            && settlementFilter
            && (settlementFilter.receiving === false)
        ) {
            body = this.props.i18n.noMonitoredParamsOnSpecifyedFilter;
        }

        if (settlementFilter && settlementFilter.receiving) {
            body = <ContentLoader margin={ 5 } size={ 75 } />;
        }

        if (settlementFilter
            && (settlementFilter.receiving === false)
            && Array.isArray(settlementFilter.avaliableSettlements)
            && (settlementFilter.avaliableSettlements.length > 0)
        ) {
            body = this.buildSettlements(settlementFilter.avaliableSettlements);
            button = <div className="form-group">
                <input type="submit" className="btn btn-default" value={ this.props.i18n.apply } />
            </div>;
        }

        return (
            <form onSubmit={this.handleSubmit.bind(this)}>
                <p><b>{ this.props.i18n.monitoredParameters }</b></p>
                { body }
                { button }
            </form>
        );
    }
}

function mapStateToProps (store) {
    return {
        settlementFilter: store.settlementFilter,
        flightFilter: store.flightFilter
    }
}

function mapDispatchToProps(dispatch) {
    return {
        changeCheckstate: bindActionCreators(changeSettlementItemCheckstateAction, dispatch),
        applySettlementFilter: bindActionCreators(applySettlementFilterAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ResultSettlementFilter);
