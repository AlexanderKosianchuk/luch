import './list.sass'

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate } from 'react-redux-i18n';

import Title from 'components/flight-events/title/Title';

import ContentLoader from 'controls/content-loader/ContentLoader';

import getFlightInfo from 'actions/getFlightInfo';

class List extends React.Component {
    componentDidMount() {
        if (this.props.pending !== false) {
            this.props.getFlightInfo({ flightId: this.props.flightId });
        }
    }

    buildList() {
        return <div>1</div>
    }

    buildBody() {
        if (this.props.pending !== false) {
            return <ContentLoader/>
        } else {
            return this.buildList();
        }
    }

    render() {
        return (
            <div className='flight-events-list'>
                <Title flightInfo={ this.props.flightInfo.data }/>
                { this.buildBody() }
            </div>
        );
    }
}

function mapStateToProps (state) {
    return {
        pending: state.flightInfo.pending,
        flightInfo: state.flightInfo,
        flightEvents: state.flightEvents
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightInfo: bindActionCreators(getFlightInfo, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(List);
