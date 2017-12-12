import './flight-comment.sass'

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import { Translate, I18n } from 'react-redux-i18n';

import Checkbox from 'controls/checkbox/Checkbox';
import ContentLoader from 'controls/content-loader/ContentLoader';

import Admission from 'components/flight-events/admission/Admission';

import request from 'actions/request';

class FlightComment extends Component {
    constructor(props) {
        super(props);

        this.textarea = null;

        this.state = {
            fetched: false,
            comment: '',
            commanderAdmitted: false,
            aircraftAllowed: false,
            generalAdmission: false
        };
    }

    componentDidMount() {
        this.props.request(
            ['flightComment', 'getComment'],
            'GET',
            null,
            { flightId: this.props.flightId}
        ).then((resp) => {
            this.setState({
                fetched: true,
                comment: resp.comment,
                commanderAdmitted: resp.commanderAdmitted,
                aircraftAllowed: resp.aircraftAllowed,
                generalAdmission: resp.generalAdmission
            });
        });
    }

    handleChangeComment(event) {
        this.setState({ comment: event.target.value });
    }

    handleChangeCommanderAdmitted() {
        this.setState({ commanderAdmitted: !this.state.commanderAdmitted });
    }

    handleChangeAircraftAllowed() {
        this.setState({ aircraftAllowed: !this.state.aircraftAllowed });
    }

    handleChangeGeneralAdmission() {
        this.setState({ generalAdmission: !this.state.generalAdmission });
    }

    handleSave() {
        this.setState({ fetched: null });
        this.props.request(
            ['flightComment', 'setComment'],
            'POST',
            null,
            {
                flightId: this.props.flightId,
                comment: this.state.comment,
                commanderAdmitted: this.state.commanderAdmitted,
                aircraftAllowed: this.state.aircraftAllowed,
                generalAdmission: this.state.generalAdmission
            }
        ).then((resp) => {
            this.setState({ fetched: true });
        });
    }

    renderButton() {
        if (this.state.fetched === null) {
            return <ContentLoader margin={ 1 } size={ 30 } border= { 3 } />;
        }

        if (this.props.userRole === 'admin') {
            return (<button
                className='btn btn-default'
                onClick={ this.handleSave.bind(this) }
            >
                <Translate value='flightEvents.flightComment.save'/>
            </button>);
        }

        return null;
    }

    render() {
        if (this.state.fetched === false) {
            return null
        }

        return (
            <div className='flight-events-flight-comment'>
                <div className='flight-events-flight-comment__container'>
                    <div className='row'>
                        <textarea
                            className='flight-events-flight-comment__textarea form-control'
                            disabled={ ((this.props.userRole !== 'admin') ? 'disabled' : '') }
                            value={ ((this.state.comment === null) ? '' : this.state.comment) }
                            onChange={ this.handleChangeComment.bind(this) }
                        >
                        </textarea>
                    </div>
                    <div className='flight-events-flight-comment__admissions-row row'>
                        <div className='col-md-10'>
                            <Admission
                                label={ I18n.t('flightEvents.flightComment.commanderAdmitted') }
                                disabled={ (this.props.userRole !== 'admin') }
                                checkstate={ this.state.commanderAdmitted }
                                changeCheckState={ this.handleChangeCommanderAdmitted.bind(this) }
                            />
                            <Admission
                                label={ I18n.t('flightEvents.flightComment.aircraftAllowed') }
                                disabled={ (this.props.userRole !== 'admin') }
                                checkstate={ this.state.aircraftAllowed }
                                changeCheckState={ this.handleChangeAircraftAllowed.bind(this) }
                            />
                            <Admission
                            label={ I18n.t('flightEvents.flightComment.generalAdmission') }
                                disabled={ (this.props.userRole !== 'admin') }
                                checkstate={ this.state.generalAdmission }
                                changeCheckState={ this.handleChangeGeneralAdmission.bind(this) }
                            />
                        </div>
                        <div className='flight-events-flight-comment__btn-col col-md-2'>
                            {  this.renderButton() }
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

FlightComment.propTypes = {
    flightId: PropTypes.number.isRequired
};

function mapStateToProps(state) {
    return {
        userRole: state.user.role
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightComment);
