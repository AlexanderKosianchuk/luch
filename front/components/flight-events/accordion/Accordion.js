import './accordion.sass';
import 'rc-collapse/assets/index.css';

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { I18n } from 'react-redux-i18n';
import PropTypes from 'prop-types';
import Collapse, { Panel } from 'rc-collapse';

import ContentHeader from 'components/flight-events/content-header/ContentHeader';
import Content from 'components/flight-events/content/Content';

import toggleEventsSection from 'actions/toggleEventsSection';

class Accordion extends Component {
    constructor(props) {
        super(props);
        this.codes = Object.keys(props.items);
    }

    handleClick(expandedSections) {
        this.props.toggleEventsSection({ expandedSections: expandedSections });
    }

    render() {
        return <Collapse
            accordion={ false }
            defaultActiveKey={ this.codes }
            className='flight-events-accordion'
            onChange={ this.handleClick.bind(this) }
        >
            {
                this.codes.map((code) => {
                    return (
                        <Panel header={ I18n.t('flightEvents.collapse.eventCodeMask' + code) + ' - ' + code }
                            key={ code }
                            className='container-fluid flight-events-accordion__container'
                        >
                            <ContentHeader />
                            <Content
                                rows={ this.props.items[code] }
                                flightId={ this.props.flightId }
                            />
                        </Panel>
                    );
                })
            }
        </Collapse>;
    }
}

Accordion.propTypes = {
    flightId: PropTypes.number.isRequired,
    items: PropTypes.object.isRequired
};

function mapStateToProps(state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        toggleEventsSection: bindActionCreators(toggleEventsSection, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(Accordion);
