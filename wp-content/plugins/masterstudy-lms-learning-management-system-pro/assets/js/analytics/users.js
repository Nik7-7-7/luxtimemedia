(function($) {
    // Fetch data global variables
    let chartsData = null;
    let usersChart = null;
    let instructorsChart = null;
    let instructorsTable = null;
    let studentsTable = null;
    let currentSearchValue = null;

    // Fetch data
    fetchDataCharts();

    // Listen for the custom search event
    document.addEventListener('intentTableSearch', function(event) {
        currentSearchValue = event.detail.searchValue;
        if($(event.detail.searchTarget).parents('.masterstudy-analytics-users-page-table').data('chart-id') === 'instructors-table')
            updateTable(routes.usersInstructorTable, true);
        if($(event.detail.searchTarget).parents('.masterstudy-analytics-users-page-table').data('chart-id') === 'students-table')
            updateTable(routes.usersStudentTable, true);
    });

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
        updateTable(routes.usersStudentTable, true);
        updateTable(routes.usersInstructorTable, true);
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-users');

        //Update data
        updateCharts();
        updateTable(routes.usersInstructorTable);
        updateTable(routes.usersStudentTable);
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-users-page-line');
            showLoaders('.masterstudy-analytics-users-page-stats');
        }

        api.get( routes.usersCharts ).then(result => {
            if (result.error_code) {
                return
            }

            chartsData = {
                total: result.total_users,
                students: result.total_students,
                instructors: result.total_instructors,
                instructors_chart: {
                    period: result.instructors?.period,
                    items: [
                        { label: users_page_data.titles.instructors_chart, values: result.instructors?.values },
                    ]
                },
                users_chart: {
                    period: result.users?.period,
                    items: [
                        { label: users_page_data.titles.users_chart, values: result.users?.values },
                    ]
                },
            }

            updateCharts();
        })
    }

    // Update charts & table methods
    function updateCharts() {
        if (chartsData && isDomReady) {
            if (!usersChart) {
                usersChart = createChart(
                    document.getElementById('masterstudy-line-chart-users').getContext('2d'),
                    'line',
                    chartsData.users_chart.period,
                    chartsData.users_chart.items.map((item, index) => ({
                        label: item.label,
                        data: item.values
                    }))
                );
            }

            if (!instructorsChart) {
                instructorsChart = createChart(document.getElementById('masterstudy-line-chart-instructors').getContext('2d'), 'line');
            }

            updateStatsBlock('.masterstudy-stats-block_total', chartsData.total);
            updateStatsBlock('.masterstudy-stats-block_registered_students', chartsData.students);
            updateStatsBlock('.masterstudy-stats-block_instructors', chartsData.instructors);
            updateTotal('#instructors-total', chartsData.instructors);
            updateTotal('#users-total', chartsData.total);
            updateLineChart(usersChart, chartsData.users_chart.period, chartsData.users_chart.items);
            updateLineChart(instructorsChart, chartsData.instructors_chart.period, chartsData.instructors_chart.items);

            hideLoaders('.masterstudy-analytics-users-page-line');
            hideLoaders('.masterstudy-analytics-users-page-stats');
        }
    }

    function updateTable(currentRoute, reloadTable = false) {
        const dataSrc = function (json) {
            const pageInfo = $(`#masterstudy-datatable-${currentRoute}`).DataTable().page.info();
            const start = pageInfo.start;

            json.data = json.data.map((item, index) => {
                item.number = start + index + 1;
                return item;
            });

            updateTotal( `#${currentRoute}-table-total`, json.recordsTotal);

            return json.data;
        }

        const columnDefs = [
            { targets: 0, width: '30px', orderable: false },
            { targets: users_page_data[currentRoute].length - 1, orderable: false },
            {
                targets: users_page_data[currentRoute].length - 1,
                data: routes.usersInstructorTable === currentRoute ? 'instructor_id' : 'student_id',
                render: function (data, type, row) {
                    const currentUrl = window.location.href;
                    const newUrl = new URL(currentUrl);
                    const role = routes.usersInstructorTable === currentRoute ? 'instructor' : 'student';
                    newUrl.searchParams.set('user_id', data);
                    newUrl.searchParams.set('role', role);

                    return renderReportButton(newUrl);
                }
            }
        ];

        let tableToUpdate = null;

        if (routes.usersInstructorTable === currentRoute) {
            tableToUpdate = instructorsTable;
        } else if (routes.usersStudentTable === currentRoute) {
            tableToUpdate = studentsTable;
        }

        tableToUpdate = updateDataTable(
            tableToUpdate,
            `#masterstudy-datatable-${currentRoute}`,
            [`[data-chart-id="${currentRoute}-table"]`],
            currentRoute,
            users_page_data[currentRoute],
            dataSrc,
            columnDefs,
            reloadTable,
            false,
            false,
            [],
            currentSearchValue
        );

        if (routes.usersInstructorTable === currentRoute) {
            instructorsTable = tableToUpdate;
        } else if (routes.usersStudentTable === currentRoute) {
            studentsTable = tableToUpdate;
        }
    }
})(jQuery);
