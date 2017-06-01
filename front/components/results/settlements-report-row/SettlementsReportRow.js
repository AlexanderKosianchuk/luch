import './settlements-report-row.sass';

import React from 'react';
import { Translate } from 'react-redux-i18n';

export default function SettlementsReportRow (props) {
    return (
        <div className="results-settlements-report-row row">
            <div className="results-settlements-report-row__col-title">
                { props.title ? props.title : <b><Translate value='results.settlementsReportRow.title' /></b> }
            </div>
            <div className="results-settlements-report-row__col-value">
                { props.count ? props.count : <b><Translate value='results.settlementsReportRow.count' /></b> }
            </div>
            <div className="results-settlements-report-row__col-value">
                { props.min ? props.min : <b><Translate value='results.settlementsReportRow.min' /></b> }
            </div>
            <div className="results-settlements-report-row__col-value">
                { props.avg ? props.avg : <b><Translate value='results.settlementsReportRow.avg' /></b> }
            </div>
            <div className="results-settlements-report-row__col-value">
                { props.sum ? props.sum : <b><Translate value='results.settlementsReportRow.sum' /></b> }
            </div>
            <div className="results-settlements-report-row__col-value">
                { props.max ? props.max : <b><Translate value='results.settlementsReportRow.max' /></b> }
            </div>
        </div>
    );
}
