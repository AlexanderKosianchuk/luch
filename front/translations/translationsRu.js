const translationsRu = {
    ru: {
        login: {
            form: {
                welcome: 'Добро пожаловать',
                to: 'Програмный комплекс обработки и анализа полетной информации «Луч»',
                vendor: 'Авиационные технологии. Мы создаем будущее',
                userName: 'Логин пользователя',
                authorize: 'Войти',
                password: 'Пароль',
                userUnexist: 'Пользователь с указанными данными не найден'
            }
        },
        flightsTree: {
            apply: 'Принять',
            toolbar: {
                flightList: 'Перечень полетов'
            },
            menuDropdown: {
                fileMenu: 'Меню',
                expand: 'Развернуть',
                collapse: 'Свернуть',
                delete: 'Удалить',
                export: 'Экспортировать',
                process: 'Обработать',
                removeSelection: 'Отменить выделение',
                exportCoordinates: 'Выгрузить маршрут',
                events: 'События',
                params: 'Параметры',
                templates: 'Шаблоны'
            },
            flightTitle: {
                bort: 'Борт',
                voyage: 'Рейс',
                startCopyTime: 'Начало полета',
                departureAirport: 'Аэропорт вылета',
                arrivalAirport: 'Аэропорт посадки',
            },
            flightControls: {
                confirm: 'Подтвердите удаление полета',
            },
            folderControls: {
                confirm: 'Подтвердите удаление каталога',
            },
        },
        flightsTable: {
            toolbar: {
                flightList: 'Перечень полетов'
            },
            menuDropdown: {
                fileMenu: 'Меню',
                delete: 'Удалить',
                export: 'Экспортировать',
                process: 'Обработать',
                removeSelection: 'Отменить выделение',
                exportCoordinates: 'Выгрузить маршрут',
                events: 'События',
                params: 'Параметры',
                templates: 'Шаблоны'
            },
            table: {
                bort: 'Борт',
                voyage: 'Рейс',
                performer: 'Исполнитель',
                startCopyTime: 'Начало полета',
                departureAirport: 'Аэропорт вылета',
                arrivalAirport: 'Аэропорт посадки'
            }
        },
        uploadingPreview: {
            toolbar: {
                preview: 'Предпросмотр'
            }
        },
        settings: {
            list: {
                options: 'Пользовательские настройки',
                save: 'Сохранить',
                printTableStep: 'Шаг цифропечати',
                mainChartColor: 'Заливка графика',
                lineWidth: 'Толщина линий на графике',
                flightShowAction: 'Действие с полетом по умолчанию'
            }
        },
        results: {
            toolbar: {
                aggregatedStatistics: 'Агрегированая статистика',
            },
            flightFilter: {
                flightInfoFilter: 'Фильтр по полетным данным',
                apply: 'Принять',
                fdrType: 'Тип регистратора',
                bort: 'Номер борта',
                voyage: 'Рейс',
                departureAirport: 'Аэропорт вылета',
                arrivalAirport: 'Аэропорт посадки',
                departureFromDate: 'От даты отправления',
                departureToDate: 'До даты отправления'
            },
            settlementFilter: {
                apply: 'Принять',
                putFlightFilter: 'Задайте параметры фильтра',
                noMonitoredParamsOnSpecifyedFilter: 'Нет отслеживаемых параметров по указанному фильтру',
                monitoredParameters: 'Отслеживаемые параметры',
            },
            settlementsReport: {
                settlementsReport: 'Результаты',
                setParamsForReportGenerating: 'Задайте параметры для формирования результатов',
                noDataToGenerateReport: 'Нет данных для формирования результатов',
            },
            settlementsReportRow: {
                title: 'Параметр',
                count: 'Количество',
                min: 'Мин',
                avg: 'Среднее',
                sum: 'Сумма',
                max: 'Макс',
            },
        },
        flightTemplates: {
            item: {
                events: 'События',
                default: 'По умолчанию',
                last: 'Крайний обзор'
            }
        },
        flightEvents: {
            formPrint: {
                grayscale: 'Ч/Б'
            },
            title: {
                fdrName: 'Тип регистратора',
                bort: 'Борт',
                voyage: 'Рейс',
                startCopyTime: 'Начало копии полета',
                departureAirport: 'Аэропорт вылета',
                arrivalAirport: 'Аэропорт посадки',
                centringto: 'Центровка на взлёте',
                centringlndg: 'Центровка на посадке',
                weightto: 'Вес на взлете',
                weightlndg: 'Вес на посадке',
                tto: 'Тнв на взлете',
                capitan: 'КВС',
                route: 'Маршрут',
                centring: 'Центровка',
            },
            list: {
                processingNotPerformed: 'Обработка не выполнена',
                noEvents: 'События не зафиксированы'
            },
            collapse: {
                eventCodeMask000: 'Технологические сообщения',
                eventCodeMask001: 'Контроль техники пилотирования',
                eventCodeMask002: 'Контроль работоспособности',
                eventCodeMask003: 'Информационные сообщения',
            },
            contentHeader: {
                start: 'Начало',
                end: 'Конец',
                duration: 'Длительность',
                code: 'Код',
                eventName: 'Название',
                algorithm: 'Алгоритм',
                aditionalInfo: 'Доп. инф.',
                reliability: 'Достов.',
                comment: 'Комментарий',
            }
        },
        calibration: {
            title: 'Градуировки',
            fdr: 'Тип регистратора',
            create: 'Создать',
            creationForm: 'Форма создатания градуировки',
            for: 'для',
            name: 'Название',
            paramName: 'Название',
            paramCode: 'Код',
            paramChannels: 'Каналы',
            valueAdd: 'Добавить',
            save: 'Сохранить',
            update: 'Обновить',
            list: 'К списку',
            unexist: 'Градуировки отсутствуют',
            dateCreation: 'Дата создания',
            dateLastEdit: 'Дата последенего редактирования',
            controls: 'Управление',
            edit: 'Редактировать',
            delete: 'Удалить',
            inputName: 'Введите название калибровки для сохранения'
        },
        flightUploader: {
            upload: 'Загрузить',
            filesList: 'Перечень файлов',
        },
        usersTable: {
            toolbar: {
                list: 'Перечень пользователей',
            },
            table: {
                login: 'Логин',
                name: 'Имя',
                email: 'Эл. почта',
                phone: 'Тел.',
                lang: 'Язык',
                role: 'Роль',
                organization: 'Организация',
                logo: 'Логотип',
                add: 'Создать',
                edit: 'Редактировать',
                delete: 'Удалить',
                list: 'Перечень',
                save: 'Сохранить',
                confimUserDeletion: 'Подтвердите удаление пользователей'
            }
        },
        userForm: {
            toolbar: {
                create: 'Cоздание нового пользователей',
                edit: 'Редактирование пользователя'
            },
            form: {
                login: 'Логин',
                name: 'Имя',
                email: 'Эл. почта',
                phone: 'Тел.',
                pass: 'Пароль',
                repeatPass: 'Повторно пароль',
                organization: 'Организация',
                role: 'Роль',
                admin: 'Администратор',
                moderator: 'Модератор',
                user: 'Пользователь',
                logo: 'Логотип',
                avaliableFdrs: 'Доступ к типам регистраторов',
                chooseFile: 'Выберите файл',
                notAllNecessarySent: 'Не все обязательные поля заполнены (отмечены *)',
                alreadyExist: 'Пользователь с выбраным логином уже существует',
                creationError: 'Ошибка создания пользователя'
            }
        },
        topMenu: {
            fileImport: 'Импортировать',
            upload: 'Загрузить',
            flightImporterDropdown: {
                fileImport: 'Импортировать',
                chooseFile: 'Выбрать файл',
            },
            flightUploaderDropdown: {
                flightUploading: 'Загрузка полета',
                chooseFile: 'Выбрать файл',
                preview: 'Предпросмотр',
                on: 'Вкл',
                off: 'Выкл',
            }
        },
        cycloParams: {
            colorPicker: {
                cancel: 'Отменить',
                save: 'Сохранить'
            }
        },
        mainMenu: {
            flights: 'Полеты',
            fdrs: 'Регистраторы',
            calibration: 'Градуировки',
            users: 'Пользователи',
            results: 'Результаты',
            search: 'Поиск',
        },
        flightViewOptionsSwitch: {
            events: 'События',
            params: 'Параметры',
            templates: 'Шаблоны'
        },
        flightListViewSwitch : {
            treeView: 'Дерево',
            tableView: 'Таблица',
        },
        flightTemplateEdit: {
            toolbar: {
                templates: 'Шаблоны'
            },
            saveForm: {
                templateName: 'Название шаблона'
            }
        },
        table: {
            previous: 'Предыдущий',
            next: 'Следующий',
            loading: 'Загрузка...',
            noRowsFound: 'Элементы не найдены',
            page: 'Страница',
            of: 'из',
            rows: 'строк',
        }
    }
};

export default translationsRu;
