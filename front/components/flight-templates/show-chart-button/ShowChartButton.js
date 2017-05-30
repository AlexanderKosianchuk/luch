import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';

import mergeTemplates from 'actions/mergeTemplates';
import redirect from 'actions/redirect';

class ShowChartByTemplates extends React.Component {
    buildButton() {
        if (_isEmpty(this.props.chosenTemplates.list)) {
            return '';
        }

        return <span
            className="glyphicon glyphicon-picture"
            aria-hidden="true">
        </span>;
    }

    showChart()
    {
        if (this.props.chosenTemplates.list.length === 1) {
            this.props.redirect('/chart/'
                + 'flight-id/'+ this.props.flightId + '/'
                + 'template-name/'+ this.props.chosenTemplates.list[0] + '/'
                + 'from-frame/'+ this.props.startFrame + '/'
                + 'to-frame/'+ this.props.endFrame
            );
        } else if (this.props.chosenTemplates.list.length > 1) {
            let templateName = 'last';
            Promise.resolve(this.props.mergeTemplates({
                flightId: this.props.flightId,
                resultTemplateName: templateName,
                templatesToMerge: this.props.chosenTemplates.list
            })).then(() => {
                this.props.redirect('/chart/'
                    + 'flight-id/'+ this.props.flightId + '/'
                    + 'template-name/'+ templateName + '/'
                    + 'from-frame/'+ this.props.startFrame + '/'
                    + 'to-frame/'+ this.props.endFrame
                );
            });
        }
    }

    render() {
        return <ul className="nav navbar-nav navbar-right">
          <li><a href="#" onClick={ this.showChart.bind(this) }>
              { this.buildButton() }
          </a></li>
        </ul>;
    }
}

function mapStateToProps (state) {
    return {
        chosenTemplates: state.chosenTemplates,
        startFrame: state.flightInfo.selectedStartFrame,
        endFrame: state.flightInfo.selectedEndFrame
    }
}

function mapDispatchToProps(dispatch) {
    return {
        mergeTemplates: bindActionCreators(mergeTemplates, dispatch),
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ShowChartByTemplates);
