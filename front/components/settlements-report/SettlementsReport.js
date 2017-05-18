import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate, I18n } from 'react-redux-i18n';

import ContentLoader from 'components/content-loader/ContentLoader';
import SettlementsReportRow from 'components/settlements-report-row/SettlementsReportRow';

class SettlementsReport extends React.Component {
    buildReport(pending, report) {
        if (pending === null) {
            return I18n.t('settlementsReport.setParamsForReportGenerating');
        }

        if (pending) {
            return <ContentLoader margin={ 5 } size={ 75 } />;;
        }

        if (!report || report.length === 0) {
            return I18n.t('settlementsReport.noDataToGenerateReport');
        }

        let rows = [];
        rows.push(
            <SettlementsReportRow key="title" i18n={ this.props.i18n } />
        );
        report.forEach((item, index) => {
            if (item.text && item.values) {
                let count = item.values.length
                let sum = item.values.reduce((next, sum) => next + sum, 0);
                let avg = sum / count;
                let min = Math.min.apply(null, item.values);
                let max = Math.max.apply(null, item.values);
                rows.push(
                    <SettlementsReportRow
                        key={ index }
                        title={ item.text }
                        count={ count }
                        sum={ sum }
                        avg={ avg }
                        min={ min }
                        max={ max }
                    />
                );
            }
        });

        return rows;
    }

    render() {
        let body = this.buildReport(this.props.pending, this.props.report);
        return (
            <div>
                <p><b><Translate value='settlementsReport.settlementsReport' /></b></p>
                { body }
            </div>
        );
    }
}

function mapStateToProps (store) {
    return {
        pending: store.settlementsReport.pending,
        report: store.settlementsReport.report
    }
}

export default connect(mapStateToProps)(SettlementsReport);
