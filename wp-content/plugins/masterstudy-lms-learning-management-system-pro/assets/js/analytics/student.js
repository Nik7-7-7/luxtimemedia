(function($) {
    // Fetch data global variables
    let chartsData = null;
    let coursesChart = null;
    let coursesTable = null;
    let membershipTable = null;

    // Fetch data
    fetchDataCharts();

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
        updateTable(routes.studentCoursesTable, true);
        if (student_page_data.is_membership_active) {
            updateTable(routes.studentMembershipTable, true);
        }
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-student');

        //Update data
        updateCharts();
        updateTable(routes.studentCoursesTable);
        if (student_page_data.is_membership_active) {
            updateTable(routes.studentMembershipTable);
        }
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-student-page-line');
            showLoaders('.masterstudy-analytics-student-page-stats');
            showLoaders('.masterstudy-analytics-student-page-types');
        }

        api.get( routes.studentCharts ).then(result => {
            if (result.error_code) {
                return
            }

            chartsData = {
                revenue: result.revenue,
                orders: result.orders,
                membership_plan: '',
                courses: {
                    enrolled: result.courses.enrolled,
                    not_started: result.courses.not_started,
                    in_progress: result.courses.in_progress,
                    completed: result.courses.completed,
                },
                quizzes: {
                    in_progress: result.quizzes.in_progress,
                    pending: result.quizzes.pending,
                    passed: result.quizzes.passed,
                    failed: result.quizzes.failed,
                },
                assignments: {
                    passed: result.assignments.passed,
                    failed: result.assignments.failed,
                },
                bundles: result.bundles,
                groups: result.groups,
                reviews: result.reviews,
                certificates: result.certificates,
                points: result.points,
                courses_chart: {
                    period: result.enrollments.period,
                    items: [
                        { label: student_page_data.titles.courses_chart.enrolled, values: result.enrollments.all },
                        { label: student_page_data.titles.courses_chart.completed, values: result.enrollments.completed },
                    ]
                },
            }

            updateCharts();
        })
    }

    // Update charts & table methods
    function updateCharts() {
        if (chartsData && isDomReady) {
            if (!coursesChart) {
                coursesChart = createChart(
                    document.getElementById('masterstudy-line-chart-courses').getContext('2d'),
                    'line',
                    chartsData.courses_chart.period,
                    chartsData.courses_chart.items.map((item, index) => ({
                        label: item.label,
                        data: item.values
                    }))
                );
            }
            updateLineChart(coursesChart, chartsData.courses_chart.period, chartsData.courses_chart.items);
            // revenue stats blocks
            updateStatsBlock('.masterstudy-stats-block_revenue', chartsData.revenue, 'currency');
            updateStatsBlock('.masterstudy-stats-block_orders', chartsData.orders);
            updateStatsBlock('.masterstudy-stats-block_membership_plan', chartsData.membership_plan);
            // courses stats blocks
            updateStatsBlock('.masterstudy-stats-block_enrolled', chartsData.courses.enrolled);
            updateStatsBlock('.masterstudy-stats-block_completed', chartsData.courses.completed);
            updateStatsBlock('.masterstudy-stats-block_in_progress', chartsData.courses.in_progress);
            updateStatsBlock('.masterstudy-stats-block_not_started', chartsData.courses.not_started);
            // quizzes stats blocks
            updateStatsBlock('.masterstudy-analytics-student-page-stats__block_quizzes .masterstudy-stats-block_passed', chartsData.quizzes.passed);
            updateStatsBlock('.masterstudy-analytics-student-page-stats__block_quizzes .masterstudy-stats-block_failed', chartsData.quizzes.failed);
            // assignments stats blocks
            updateStatsBlock('.masterstudy-analytics-student-page-stats__block_assignments .masterstudy-stats-block_passed', chartsData.assignments.passed);
            updateStatsBlock('.masterstudy-analytics-student-page-stats__block_assignments .masterstudy-stats-block_failed', chartsData.assignments.failed);
            // others stats blocks
            updateStatsBlock('.masterstudy-stats-block_bundles', chartsData.bundles);
            updateStatsBlock('.masterstudy-stats-block_groups', chartsData.groups);
            updateStatsBlock('.masterstudy-stats-block_reviews', chartsData.reviews);
            updateStatsBlock('.masterstudy-stats-block_certificates', chartsData.certificates);
            updateStatsBlock('.masterstudy-stats-block_points', chartsData.points);

            hideLoaders('.masterstudy-analytics-student-page-line');
            hideLoaders('.masterstudy-analytics-student-page-stats');
            hideLoaders('.masterstudy-analytics-student-page-types');
        }
    }

    function updateTable(currentRoute, reloadTable = false) {
        const routeKey = currentRoute.split('/').pop();
        const dataSrc = function (json) {
            const pageInfo = $(`#masterstudy-datatable-${routeKey}`).DataTable().page.info();
            const start = pageInfo.start;

            json.data = json.data.map((item, index) => {
                item.number = start + index + 1;

                if (item.hasOwnProperty('price')) {
                    item.price = formatCurrency(item.price);
                }

                return item;
            });

            return json.data;
        }

        let columnDefs = [{ targets: 0, width: '30px', orderable: false }];

        if (routes.studentCoursesTable === currentRoute) {
            columnDefs = [
                ...columnDefs,
                { targets: 2, orderable: false },
                {
                    targets: student_page_data.courses.length - 1,
                    data: 'rating',
                    render: function (data, type, row) {
                        return renderProgress(data);
                    }
                }
            ];
        }

        let tableToUpdate = null;

        if (routes.studentCoursesTable === currentRoute) {
            tableToUpdate = coursesTable;
        } else if (routes.studentMembershipTable === currentRoute) {
            tableToUpdate = membershipTable;
        }

        tableToUpdate = updateDataTable(
            tableToUpdate,
            `#masterstudy-datatable-${routeKey}`,
            [`[data-chart-id="${routeKey}-table"]`],
            currentRoute,
            student_page_data[routeKey],
            dataSrc,
            columnDefs,
            reloadTable
        );

        if (routes.studentCoursesTable === currentRoute) {
            coursesTable = tableToUpdate;
        } else if (routes.studentMembershipTable === currentRoute) {
            membershipTable = tableToUpdate;
        }
    }
})(jQuery);