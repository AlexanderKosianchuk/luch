import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import ContentLoader from 'controls/content-loader/ContentLoader'
import CycloParams from 'controls/cyclo-params/CycloParams';

import getTemplate from 'actions/getTemplate';
import setCheckedFlightParams from 'actions/setCheckedFlightParams';

class Params extends React.Component {
    constructor(props)
    {
        super(props);

        this.state = {
            isReady: false
        }

        this.templateAnalogParams = [];
        this.templateBinaryParams = [];
    }

    componentWillMount()
    {
        this.props.getTemplate({
            flightId: this.props.flightId,
            templateName: this.props.templateName
        }).then((responce) => {
            this.templateAnalogParams = responce.ap || [];
            this.templateBinaryParams = responce.bp || [];
            this.props.setCheckedFlightParams({
                ap: this.templateAnalogParams,
                bp: this.templateBinaryParams
            });
            this.setState({isReady: true})
        });
    }

    buildBody()
    {
        if (this.state.isReady === true) {
            return <CycloParams
                flightId={ this.props.flightId }
                colorPickerEnabled={ false }
                checkedAnalogParams={ this.templateAnalogParams }
                checkedBinaryParams={ this.templateBinaryParams }
            />;
        } else {
            return <ContentLoader/>;
        }
    }

    render () {
        return (
            <div>
                { this.buildBody() }
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        pending: state.templatesList.pending
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getTemplate: bindActionCreators(getTemplate, dispatch),
        setCheckedFlightParams: bindActionCreators(setCheckedFlightParams, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Params);
