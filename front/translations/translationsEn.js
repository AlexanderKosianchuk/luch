const translationsEn = {
    en: {
        login: {
            form: {
                welcome: 'Welcome',
                to: 'Flight data processing and analysis software complex «Luche»',
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
            title: 'Calibration',
            fdr: 'FDR',
            create: 'Create',
            creationForm: 'Calibration Creation Form',
            for: 'for',
            name: 'Name',
            paramName: 'Name',
            paramCode: 'Code',
            paramChannels: 'Channels',
            valueAdd: 'Add',
            save: 'Save',
            update: 'Update',
            list: 'To List',
            unexist: 'No calibrations',
            dateCreation: 'Creation Date',
            dateLastEdit: 'Last Edit',
            controls: 'Controls',
            edit: 'Edit',
            delete: 'Delete',
            inputName: 'Please input name to save'
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
                organization: 'Organization',
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
                organization: 'Organization',
                role: 'Role',
                admin: 'Admin',
                moderator: 'Moderator',
                user: 'User',
                logo: 'Logo',
                chooseFile: 'Choose logo'
            }
        },
        topMenu: {
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
