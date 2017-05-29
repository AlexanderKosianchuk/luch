import './flight-templates-item.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Collapse } from 'react-collapse';

import FlightTemplatesItemControls from 'components/flight-templates-item-controls/FlightTemplatesItemControls';
import FlightTemplatesItemDescription from 'components/flight-templates-item-description/FlightTemplatesItemDescription';
import redirect from 'actions/redirect';

class FlightTemplatesItem extends React.Component {
    constructor(props)
    {
        super(props);
        this.state = {
            isOpened: false
        };
    }

    checkServicePurpose(servicePurpose)
    {
        let glyphicon = '';
        if (servicePurpose.isDefault) {
            glyphicon = 'glyphicon-home';
        } else if (servicePurpose.isEvents) {
            glyphicon = 'glyphicon-flag';
        } else if (servicePurpose.isLast) {
            glyphicon = 'glyphicon-retweet';
        }

        return <span className={ 'glyphicon flight-templates-item__glyphicon ' + glyphicon }></span>;
    }

    render ()
    {
        return (
            <div className='flight-templates-item'>
                <div className='row'>
                    <div className='col-sm-1 flight-templates-item__service-purpose-col'>
                        { this.checkServicePurpose(this.props.servicePurpose) }
                    </div>

                    <div className='col-sm-2'>
                        <span className='flight-templates-item__title'>{ this.props.name }</span>
                    </div>

                    <div className='col-sm-2'>
                        <FlightTemplatesItemControls
                            servicePurpose={ this.props.servicePurpose }
                            flightId={ this.props.flightId }
                            templateName={ this.props.name }
                        />
                    </div>

                    <div className='col-sm-6'>
                        <span className='flight-templates-item__body'>{ this.props.paramCodes }</span>
                    </div>

                    <div className='col-sm-1'>
                        <span className={ 'glyphicon flight-templates-item__glyphicon '
                                + (this.state.isOpened
                                ? 'glyphicon-chevron-up'
                                : 'glyphicon-chevron-down')
                            }
                            onClick={() => { this.setState({isOpened: !this.state.isOpened}) }}
                        >
                        </span>
                    </div>

                    <div className='row'>
                        <div className='col-sm-12'>
                            <Collapse isOpened={ this.state.isOpened }>
                                <FlightTemplatesItemDescription params={ this.props.params }/>
                            </Collapse>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplatesItem);
