import './settlements-report-row.sass';

import React from 'react';
import { Translate } from 'react-redux-i18n';

export default function SettlementsReportRow (props) {
    return (
        <div className="settlements-report-row row">
            <div className="settlements-report-row__col-title">
                { props.title ? props.title : <b><Translate value='settlementsReportRow.title' /></b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.count ? props.count : <b><Translate value='settlementsReportRow.count' /></b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.min ? props.min : <b><Translate value='settlementsReportRow.min' /></b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.avg ? props.avg : <b><Translate value='settlementsReportRow.avg' /></b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.sum ? props.sum : <b><Translate value='settlementsReportRow.sum' /></b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.max ? props.max : <b><Translate value='settlementsReportRow.max' /></b> }
            </div>
        </div>
    );
}
