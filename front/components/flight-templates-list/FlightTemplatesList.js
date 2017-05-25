import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import ContentLoader from 'components/content-loader/ContentLoader';
import getFlightTemplates from 'actions/getFlightTemplates';

class FlightTemplatesList extends React.Component {
    componentWillMount()
    {

    }

    render () {
        return (
            <div>
                <ContentLoader/>
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightTemplates: bindActionCreators(getFlightTemplates, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplatesList);
