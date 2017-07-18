/*jslint browser: true*/
/*global $, jQuery, FlightUploader, Chart, User*/

'use strict';

// libs
import 'jquery';
import 'jquery-ui';
import 'jquery-ui/ui/widgets/button';
import 'jquery-ui/ui/widgets/menu';

import 'datatables';
import 'bootstrap-loader';

// lib styles
import 'jquery-ui/themes/base/all.css';

//old styles
import 'stylesheets/style.css';

// old prototypes
import FlightUploader from 'FlightUploader';
import ChartService from 'Chart';
import User from 'User';
import Calibration from 'Calibration';

import { push } from 'react-router-redux'
import startFlightUploading from 'actions/particular/startFlightUploading';

export default function facade(store) {
    $(document).on('importItem', function (e, form) {
        let dfd = $.Deferred();
        let FU = new FlightUploader(store);
        FU.Import(form, dfd);
        dfd.promise();

        dfd.then(() => {
            // TODO add item to redux flightsList
        });
    });

    $(document).on('uploadWithPreview', function (e, showcase, uploadingUid, fdrId, calibrationId) {
        let FU = new FlightUploader(store);
        FU.FillFactoryContaider(showcase, uploadingUid, fdrId, calibrationId);
    });

    $(document).on('startProccessing', function (e, uploadingUid) {
        store.dispatch(startFlightUploading({
            uploadingUid: uploadingUid
        }));
    });

    $(document).on('endProccessing', function (e, uploadingUid, item) {
        store.dispatch({
            type: 'FLIGHT_UPLOADING_COMPLETE',
            payload: {
                uploadingUid: uploadingUid,
                item: item
            }
        });
    });

    $(document).on('chartShow', function (
        e, showcase,
        flightId, tplName,
        stepLength, startCopyTime,
        startFrame, endFrame,
        apParams, bpParams
    ) {
        var C = new ChartService(store);
        C.SetChartData(
            flightId, tplName,
            stepLength, startCopyTime,
            startFrame, endFrame,
            apParams, bpParams
        );
        C.FillFactoryContaider(showcase);
    });

    $(document).on('changeLanguage', function (e, newLanguage) {
        let U = new User(store);
        U.changeLanguage(newLanguage);
    });

    $(document).on('calibrationsShow', function (e, showcase) {
        let CLB = new Calibration(store);
        CLB.FillFactoryContaider(showcase);
    });

    $(document).on('uploadPreviewedFlight', function(uploadingUid, fdrId, calibrationId) {
        let FU = new FlightUploader(store);
        FU.uploadPreviewed().then(() => {
            store.dispatch(push('/'));
        });
    });
}
