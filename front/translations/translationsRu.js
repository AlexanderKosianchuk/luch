const translationsRu = {
    ru: {
        userLogin: {
            userName: 'Логин пользователя',
            loginForm: 'Форма авторизации',
            authorize: 'Войти',
            password: 'Пароль'
        },
        topMenu: {
            fileImport: 'Импортировать',
            upload: 'Загрузить',
        },
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
        },
        mainMenu: {
            flights: 'Полеты',
            fdrs: 'Регистраторы',
            calibration: 'Градуировки',
            users: 'Пользователи',
            results: 'Результаты',
            search: 'Поиск',
        },
        userOptions: {
            options: 'Пользовательские настройки',
            save: 'Сохранить',
            printTableStep: 'Шаг цифропечати',
            mainChartColor: 'Заливка графика',
            lineWidth: 'Толщина линий на графике',
        },
        resultsToolbar: {
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
        resultSettlementFilter: {
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
        flightList: {
            apply: 'Принять',
        },
        flightListOptions: {
            flightList: 'Перечень полетов'
        },
        flightListTypeSwitch : {
            treeView: 'Дерево',
            tableView: 'Таблица',
        },
        flightListMenuDropdown: {
            fileMenu: 'Меню',
            deleteItem: 'Удалить',
            exportItem: 'Экспортировать',
            processItem: 'Обработать',
            removeSelection: 'Отменить выделение',
            selectAll: 'Выбрать все',
            exportCoordinates: 'Выгрузить маршрут',
            syncFlightHeaders: 'Синхронизировать опознавательные данные',
        },
        flightListTree: {
            create: 'Создать',
            rename: 'Переименовать',
            remove: 'Удалить'
        },
        flightViewOptionsSwitch: {
            events: 'События',
            params: 'Параметры',
            templates: 'Шаблоны'
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
            inputName: 'Введите название калибровки для сохранения',
        },
        flightUploader: {
            upload: 'Загрузить',
            filesList: 'Перечень файлов',
        },
        searchFlights: {
            title: 'Поиск полетов',
            applyAlgorithm: 'Найти'
        },
        user: {
            actions: 'Действия',
            add: 'Создать',
            edit: 'Редактировать',
            delete: 'Удалить',
            list: 'Перечень',
            save: 'Сохранить',
            cancel: 'Отменить',
            confimUserDeletion: 'Подтвердите удаление пользователей',
            creaitonFailServerError: 'Ошибка связи при попытке создания пользователя. Попробуйте позже.',
        },
        colorpicker: {
            ok: 'Принять',
            cancel: 'Отменить',
            none: 'Никакой',
            button: 'Цвет',
            title: 'Выбрать цвет',
            transparent: 'Прозрачный',
        },
        dataTable: {
            sLengthMenu: 'Показывать по _MENU_ записей на странице',
            sZeroRecords: 'Поиск не дал результата',
            sInfo: 'Показано с _START_ по _END_ запись из  _TOTAL_ ',
            sInfoEmpty: 'Записи Non',
            sInfoFiltered: '(всего _MAX_ )',
            sSearch: 'Поиск ',
            sProcessing: 'Получение данных...',
            oPaginate: {
                sFirst: 'Начало',
                sNext: 'Следующий',
                sPrevious: 'Предыдущий',
                sLast: 'Конец'
            }
        },
    }
};

export default translationsRu;
