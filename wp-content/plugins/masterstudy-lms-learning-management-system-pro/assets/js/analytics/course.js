(function($) {
    // Fetch data global variables
    let chartsData = null;
    let revenueChart = null;
    let enrollmentsChart = null;
    let preordersChart = null;
    let enrollmentsStatusChart = null;
    let assignmentsChart = null;
    let table = null;
    let lessonsTable = null;
    const defaultUserLessonsColumns = [...course_page_data.user_lessons];
    // Store the search value
    let currentSearchValue = null;
    let currentSearchValueLessons = null;

    // Fetch data
    fetchDataCharts();

    // Listen for the custom search event
    document.addEventListener('intentTableSearch', function(event) {
        if($(event.detail.searchTarget).parents('.masterstudy-analytics-course-page-table').data('chart-id') === 'lessons-table')
            currentSearchValue = event.detail.searchValue;
        if($(event.detail.searchTarget).parents('.masterstudy-analytics-course-page-table').data('chart-id') === 'lessons-users')
            currentSearchValueLessons = event.detail.searchValue;
        updateTable(true);
    });

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
        updateTable(true);
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-course');
        if ($.fn.select2) {
            const $selectElement = $('select.masterstudy-analytics-course-page-table__filter');
            if ($selectElement.data('select2')) {
                $selectElement.select2('destroy');
            }
        }
        //Update data
        updateCharts();
        updateTable();
    });

    document.querySelectorAll('.masterstudy-analytics-course-page-table-select-filters select').forEach(function(element) {
        element.addEventListener('change', function (event) {
            updateTable(true);
        });
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-course-page-line');
            showLoaders('.masterstudy-analytics-course-page-stats');
            showLoaders('.masterstudy-analytics-course-page-doughnut');
        }

        api.get( routes.courseCharts ).then(result => {
            if (result.error_code) {
                return
            }

            chartsData = {
                revenue: result.total_revenue,
                total_enrollments: result.total_enrollments,
                reviews: result.reviews,
                certificates: result.certificates,
                orders_count: result.orders_count,
                lessons: 0,
                preorders: result.preorders_count,
                subscribers: result.subscribers_count,
                assignments: result.total_assignments,
                revenue_chart: {
                    period: result.earnings?.period,
                    items: [
                        { label: course_page_data.titles.revenue_chart, values: result.earnings?.values },
                    ]
                },
                enrollments_chart: {
                    period: result.enrollments.period,
                    items: [
                        { label: course_page_data.titles.enrollments_chart, values: result.enrollments.values },
                    ]
                },
                preorders_chart: {
                    period: result.preorders?.period,
                    items: [
                        { label: course_page_data.titles.email_subscribers, values: result.subscribers?.values },
                        { label: course_page_data.titles.preorders_chart, values: result.preorders?.values },
                    ]
                },
                enrollments_status: {
                    labels: course_page_data.titles.enrollments_status,
                    values: [result.courses_by_status.not_started, result.courses_by_status.in_progress, result.courses_by_status.completed],
                    percents: getPercentesByValues(
                        [result.courses_by_status.not_started, result.courses_by_status.in_progress, result.courses_by_status.completed]
                    ),
                },
                assignments_chart: {
                    labels: course_page_data.titles.assignments_chart,
                    values: Object.values(result.assignments),
                    percents: getPercentesByValues(Object.values(result.assignments)),
                }
            }

            updateCharts();
        })
    }

    // Update charts & table methods
    function updateCharts() {
        if (chartsData && isDomReady) {
            if (!revenueChart) {
                revenueChart = createChart(document.getElementById('masterstudy-line-chart-revenue').getContext('2d'), 'line', [], [], true);
            }
            if (!preordersChart && stats_data.upcoming_addon) {
                preordersChart = createChart(
                    document.getElementById('masterstudy-line-chart-preorders').getContext('2d'),
                    'line',
                    chartsData.preorders_chart.period,
                    chartsData.preorders_chart.items.map((item, index) => ({
                        label: item.label,
                        data: item.values
                    }))
                );
            }
            if (!enrollmentsChart) {
                enrollmentsChart = createChart(document.getElementById('masterstudy-line-chart-enrollments').getContext('2d'), 'line');
            }
            if (!enrollmentsStatusChart) {
                enrollmentsStatusChart = createChart(document.getElementById('masterstudy-doughnut-chart-enrollments-status').getContext('2d'), 'doughnut');
            }
            if (!assignmentsChart && stats_data.assignments_addon) {
                assignmentsChart = createChart(document.getElementById('masterstudy-doughnut-chart-assignments').getContext('2d'), 'doughnut');
            }

            updateStatsBlock('.masterstudy-stats-block_revenue', chartsData.revenue, 'currency');
            updateStatsBlock('.masterstudy-stats-block_enrollments', chartsData.total_enrollments);
            updateStatsBlock('.masterstudy-stats-block_reviews', chartsData.reviews);
            updateStatsBlock('.masterstudy-stats-block_certificates', chartsData.certificates);
            updateStatsBlock('.masterstudy-stats-block_orders', chartsData.orders_count);
            updateTotal('#revenue-total', chartsData.revenue, 'currency');
            updateTotal('#enrollments-total', chartsData.total_enrollments);
            updateTotal('#masterstudy-chart-total-enrollments-status', chartsData.total_enrollments);
            updateTotal('#masterstudy-chart-total-assignments', chartsData.assignments);
            updateLineChart(revenueChart, chartsData.revenue_chart.period, chartsData.revenue_chart.items);
            updateLineChart(enrollmentsChart, chartsData.enrollments_chart.period, chartsData.enrollments_chart.items);
            if ( stats_data.upcoming_addon ) {
                updateTotal('#subscribers-total', chartsData.subscribers);
                updateTotal('#preorders-total', chartsData.preorders);
                updateLineChart(preordersChart, chartsData.preorders_chart.period, chartsData.preorders_chart.items);
            }
            updateDoughnutChart(enrollmentsStatusChart, chartsData.enrollments_status);
            if ( stats_data.assignments_addon ) {
                updateDoughnutChart(assignmentsChart, chartsData.assignments_chart);
            }

            hideLoaders('.masterstudy-analytics-course-page-line');
            hideLoaders('.masterstudy-analytics-course-page-stats');
            hideLoaders('.masterstudy-analytics-course-page-doughnut');
        }
    }

    function updateTable(reloadTable = false, isLessonsTable = false) {
        const dataSrc = function(json) {
            const pageInfo = $('#masterstudy-datatable-lessons').DataTable().page.info();
            const start = pageInfo.start;

            json.data = json.data.map((item, index) => {
                item.number = start + index + 1;
                return item;
            });

            updateStatsBlock('.masterstudy-stats-block_all_lessons', json.recordsTotal);
            hideLoaders('[data-id="all_lessons"]');

            const dataSrcUserLessons = function(json) {
                const pageInfo = $('#masterstudy-datatable-lessons-by-users').DataTable().page.info();
                const start = pageInfo.start;
                json.data = json.data.map((item, index) => {
                    item.number = start + index + 1;
                    return item;
                });

                return json.data;
            }

            course_page_data.user_lessons = [...defaultUserLessonsColumns];

            lessonsTable = updateDataTable(
                lessonsTable,
                '#masterstudy-datatable-lessons-by-users',
                ['[data-chart-id="lessons-users"]'],
                routes.courseLessonsUsersTable,
                course_page_data.user_lessons,
                dataSrcUserLessons,
                [],
                reloadTable,
                false,
                true,
                json.columns,
                currentSearchValueLessons
            );

            return json.data;
        };

        table = updateDataTable(
            table,
            '#masterstudy-datatable-lessons',
            [
                '[data-chart-id="lessons-table"]',
                '[data-id="all_lessons"]'
            ],
            routes.courseLessonsTable,
            course_page_data.lessons,
            dataSrc,
            [],
            reloadTable,
            false,
            false,
            [],
            currentSearchValue
        );
    }
})(jQuery);
