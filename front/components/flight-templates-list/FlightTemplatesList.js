import './flight-templates-list.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Collapse } from 'react-collapse';

import ContentLoader from 'components/content-loader/ContentLoader';
import FlightTemplatesItem from 'components/flight-templates-item/FlightTemplatesItem';
import getFlightTemplates from 'actions/getFlightTemplates';

class FlightTemplatesList extends React.Component {
    constructor(props)
    {
        super(props);
        this.state = {
            isOpened: false
        };
    }

    componentWillMount()
    {
        this.props.getFlightTemplates({ flightId: this.props.flightId });
    }

    buildTemplatesList()
    {
        let list = [];
        this.props.templates.forEach((item, index) => {
            list.push(<FlightTemplatesItem
                key={ index }
                name={ item.name }
                paramCodes={ item.paramCodes.join(', ') }
                params={ item.params }
                servicePurpose={ item.servicePurpose }
                flightId={ this.props.flightId }
            />);
        });

        return list;
    }

    buildBody()
    {
        if (this.props.templatesFetching !== false) {
            return <ContentLoader/>
        } else {
            return this.buildTemplatesList();
        }
    }

    render ()
    {
        return (
            <div className='container-fluid flight-templates-list'>
                { this.buildBody() }
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        templatesFetching: state.templates.pending,
        templates: state.templates.list,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightTemplates: bindActionCreators(getFlightTemplates, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplatesList);
