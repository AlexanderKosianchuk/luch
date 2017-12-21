const translationsEn = {
    en: {
        login: {
            form: {
                welcome: 'Welcome',
                to: 'Flight data processing and analysis software complex «Luch»',
                vendor: 'Aviation Technologies. We create future',
                userName: 'User name',
                authorize: 'Log in',
                password: 'Password',
                userUnexist: 'User unexist'
            }
        },
        flightsTree: {
            apply: 'Apply',
            toolbar: {
                flightList: 'Flights list'
            },
            menuDropdown: {
                fileMenu: 'Menu',
                expand: 'Expand',
                collapse: 'Collapse',
                delete: 'Delete',
                export: 'Export',
                process: 'Process',
                removeSelection: 'Cancel selection',
                exportCoordinates: 'Export coordinates',
                events: 'Events',
                params: 'Parameters',
                templates: 'Templates'
            },
            flightTitle: {
                performer: 'Perf.',
                bort: 'Bort',
                voyage: 'Flight',
                performer: 'Performer',
                startCopyTime: 'Flight start time',
                departureAirport: 'Departure airport',
                arrivalAirport: 'Arrival airport',
                previous: 'Previous',
                next: 'Next',
                loading: 'Loading...',
                noRowsFound: 'No rows found',
                page: 'Page',
                of: 'of',
                rows: 'rows',
            },
            flightControls: {
                confirm: 'Confirm flight deleting',
            },
            folderControls: {
                confirm: 'Confirm folder deleting',
            },
        },
        flightsTable: {
            toolbar: {
                flightList: 'Flights list'
            },
            menuDropdown: {
                fileMenu: 'Menu',
                delete: 'Delete',
                export: 'Export',
                process: 'Process',
                removeSelection: 'Cancel selection',
                exportCoordinates: 'Export coordinates',
                events: 'Events',
                params: 'Parameters',
                templates: 'Templates'
            },
            table: {
                bort: 'Bort',
                voyage: 'Flight',
                startCopyTime: 'Flight start time',
                departureAirport: 'Departure airport',
                arrivalAirport: 'Arrival airport',
            }
        },
        uploadingPreview: {
            toolbar: {
                preview: 'Preview'
            }
        },
        settings: {
            list: {
                options: 'User options',
                save: 'Save',
                printTableStep: 'Print table step',
                mainChartColor: 'Main chart background color',
                lineWidth: 'Сhart lines width',
                flightShowAction: 'Flight show action'
            }
        },
        results: {
            toolbar: {
                aggregatedStatistics: 'Aggregated statistics',
            },
            flightFilter: {
                flightInfoFilter: 'Filter by flight info',
                apply: 'Apply',
                fdrType: 'FDR type',
                bort:'A/C',
                voyage:'Flight',
                departureFromDate:'Departure from date',
                departureToDate:'Departure to date',
                departureAirport: 'Departure Airport',
                arrivalAirport: 'Arrival Airport',
            },
            settlementFilter: {
                apply: 'Apply',
                putFlightFilter: 'Put flight filter',
                noMonitoredParamsOnSpecifyedFilter: 'No monitored params on specifyed filter',
                monitoredParameters: 'Monitored parameters',
            },
            settlementsReport: {
                settlementsReport: 'Report',
                setParamsForReportGenerating: 'Set params for report generating',
                noDataToGenerateReport: 'No data to generate report',
            },
            settlementsReportRow: {
                title: 'Param',
                count: 'Count',
                min: 'Min',
                avg: 'Avg',
                sum: 'Sum',
                max: 'Max',
            },
        },
        flightTemplates: {
            item: {
                events: 'Events',
                default: 'Default',
                last: 'Last viewed'
            }
        },
        flightEvents: {
            formPrint: {
                grayscale: 'Grayscale'
            },
            flightComment: {
                commanderAdmitted: 'Captain admitted',
                aircraftAllowed: 'Aircraft allowed',
                generalAdmission: 'General admission',
                save: 'Save'
            },
            title: {
                fdrName: 'FDR',
                bort: 'A/C',
                voyage: 'Flight',
                startCopyTime: 'The time and date of the flight',
                departureAirport: 'Departure Airport',
                arrivalAirport: 'Arrival Airport',
                centringto: 'Center of Gravity Takeoff',
                centringlndg: 'Center of Gravity Landing',
                weightto: 'Weight Takeoff',
                weightlndg: 'Weight Landing',
                tto: 'Тemperature Takeoff',
                capitan: 'F/O',
                route: 'Route',
                centring: 'Center of Gravity',
            },
            list: {
                processingNotPerformed: 'Processing not performed',
                noEvents: 'Events not found'
            },
            collapse: {
                eventCodeMask000: "Technological posts",
                eventCodeMask001: "Piloting equipment control",
                eventCodeMask002: "Health control",
                eventCodeMask003: "Information messages",
            },
            contentHeader: {
                start: 'Start',
                end: 'End',
                duration: 'Duration',
                code: 'Code',
                eventName: 'Name',
                algorithm: 'Algorithm',
                aditionalInfo: 'Aditional info',
                reliability: 'Reliability',
                comment: 'Comment',
            }
        },
        calibration: {
            toolbar: {
                title: 'Calibration'
            },
            table: {
                fdr: 'FDR',
                name: 'Name',
                dateCreation: 'Creation Date',
                dateLastEdit: 'Last Edit'
            },
            create: 'Create',
            creationForm: 'Calibration Creation Form',
            for: 'for',
            paramName: 'Name',
            paramCode: 'Code',
            paramChannels: 'Channels',
            valueAdd: 'Add',
            save: 'Save',
            update: 'Update',
            list: 'To List',
            unexist: 'No calibrations',
            controls: 'Controls',
            edit: 'Edit',
            delete: 'Delete',
            inputName: 'Please input name to save'
        },
        calibrationForm: {
            toolbar: {
                title: 'Calibration for FDR %{fdrName}'
            },
            param: {
                code: 'Code',
                name: 'Name (dim)',
                channels: 'Channels',
                minValue: 'Min value',
                maxValue: 'Max value'
            }
        },
        flightUploader: {
            upload: 'Upload',
            filesList: 'Files list',
        },
        usersTable: {
            toolbar: {
                list: 'Users list',
            },
            table: {
                login: 'Login',
                name: 'Name',
                email: 'Email',
                phone: 'Phone',
                lang: 'Language',
                role: 'Role',
                company: 'Organization',
                logo: 'Logo',
                create: 'Create',
                edit: 'Edit',
                delete: 'Delete',
                list: 'List',
                save: 'Save',
                confimUserDeleting: 'Confirm user deleting'
            }
        },
        userForm: {
            toolbar: {
                create: 'Create new user',
                edit: 'Edit user'
            },
            form: {
                login: 'Login',
                name: 'Name',
                email: 'Email',
                phone: 'Phone',
                pass: 'Password',
                repeatPass: 'Repeat password',
                company: 'Organization',
                role: 'Role',
                logo: 'Logo',
                avaliableFdrs: 'Set avaliable FDRs',
                chooseFile: 'Choose logo',
                notAllNecessarySent: 'Not all necessary params filled (marked by *)',
                alreadyExist: 'User with same login already exist',
                creationError: 'User creation error'
            },
            roleRadio: {
                admin: 'Admin',
                moderator: 'Moderator',
                user: 'User'
            }
        },
        userActivity: {
            toolbar: {
                list: 'User activity',
            },
            table: {
                action: 'Action',
                status: 'Status',
                message: 'Response message',
                date: 'Date'
            }
        },
        realtimeCalibration: {
            verticalToolbar: {
                connectionParams: 'Connection params',
                fdrType: 'FDR type'
            }
        },
        topMenu: {
            brand: 'Luch',
            calibrate: 'Сalibrate',
            fileImport: 'Import',
            upload: 'Upload',
            flightImporterDropdown: {
                fileImport: 'Import',
                chooseFile: 'Choose file',
            },
            flightUploaderDropdown: {
                flightUploading: 'Flight uploading',
                chooseFile: 'Choose file',
                preview: 'Preview',
                on: 'On',
                off: 'Off',
            }
        },
        fdrSelector: {
            placeholder: 'Select FDR type'
        },
        cycloParams: {
            colorPicker: {
                cancel: 'Cancel',
                save: 'Save'
            }
        },
        mainMenu: {
            flights: 'Flights',
            fdrs: 'FDRs',
            calibration: 'Calibration',
            users: 'Users',
            results: 'Results',
            search: 'Search',
        },
        flightViewOptionsSwitch: {
            events: 'Events',
            params: 'Parameters',
            templates: 'Templates'
        },
        flightListViewSwitch : {
            treeView: 'Tree',
            tableView: 'Table',
        },
        flightTemplateEdit: {
            toolbar: {
                templates: 'Templates'
            },
            saveForm: {
                templateName: 'Template Name'
            }
        },
        table: {
            previous: 'Previous',
            next: 'Next',
            loading: 'Loading...',
            noRowsFound: 'No rows found',
            page: 'Page',
            of: 'of',
            rows: 'rows',
        }
    }
};

export default translationsEn;
