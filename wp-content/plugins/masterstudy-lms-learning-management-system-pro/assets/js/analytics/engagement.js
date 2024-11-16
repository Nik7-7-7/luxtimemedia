(function($) {
    // Fetch data global variables
    let chartsData = null;
    let enrollmentsChart = null;
    let byStatusChart = null;
    let assignmentsChart = null;
    let table = null;
    // Store the search value
    let currentSearchValue = null;

    // Fetch data
    fetchDataCharts();

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
        $(`.masterstudy-tabs [data-id="${routes.engagementCoursesTable}"]`).trigger('click');
    });

    // Listen for the custom search event
    document.addEventListener('intentTableSearch', function(event) {
        currentSearchValue = event.detail.searchValue;
        const tabRoute = $(event.detail.searchTarget).parents('.masterstudy-analytics-engagement-page-table__header')
            .find('.masterstudy-tabs__item.masterstudy-tabs__item_active').data('id');
        updateTable(tabRoute, true); // Reload the table with the new search value
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-engagement');

        $('.masterstudy-analytics-table__tabs .masterstudy-tabs__item').click(function() {
            const tabRoute = $(this).data('id');
            $(this).parents('.masterstudy-analytics-engagement-page-table__wrapper')
                .find('.masterstudy-analytics-engagement-page-table__search')
                .attr('placeholder', engagement_page_data.search_placeholders[tabRoute])
            $(this).addClass('masterstudy-tabs__item_active');
            $(this).siblings().removeClass('masterstudy-tabs__item_active');
            updateTable(tabRoute, true);
        });

        $('.masterstudy-analytics-engagement-page__tabs .masterstudy-tabs__item').click(function() {
            const page = $(this).data('id');
            if ( page === 'revenue' ) {
                window.location.href = engagement_page_data.user_account_url;
            } else {
                window.location.href = `${engagement_page_data.user_account_url}${page}`;
            }
        });

        //Update data
        updateCharts();
        updateTable(routes.engagementCoursesTable);
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-engagement-page-line');
            showLoaders('.masterstudy-analytics-engagement-page-stats');
            showLoaders('.masterstudy-analytics-engagement-page-doughnut');
        }

        api.get( routes.engagementCharts ).then(result => {
            if (result.error_code) {
                return
            }

            chartsData = {
                total_enrollments: result.total_enrollments,
                new_courses: result.new_courses,
                certificates: result.certificates,
                new_students: result.new_students,
                new_assignments: result.new_assignments,
                new_lessons: result.new_lessons,
                new_quizzes: result.new_quizzes,
                new_groups_courses: result.new_groups_courses,
                new_trial_courses: result.new_trial_courses,
                unique_enrollments: result.unique_enrollments,
                total_assignments: result.total_assignments,
                enrollments_chart: {
                    period: result.enrollments.period,
                    items: [
                        { label: engagement_page_data.titles.enrollments_chart.total, values: result.enrollments.all },
                        { label: engagement_page_data.titles.enrollments_chart.unique, values: result.enrollments.unique },
                    ]
                },
                by_status: {
                    labels: engagement_page_data.titles.by_status,
                    values: [result.courses_by_status.not_started, result.courses_by_status.in_progress, result.courses_by_status.completed],
                    percents: getPercentesByValues(
                        [result.courses_by_status.not_started, result.courses_by_status.in_progress, result.courses_by_status.completed]
                    ),
                },
                assignments: {
                    labels: engagement_page_data.titles.assignments,
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
            if (!enrollmentsChart) {
                enrollmentsChart = createChart(
                    document.getElementById('masterstudy-line-chart-enrollments').getContext('2d'),
                    'line',
                    chartsData.enrollments_chart.period,
                    chartsData.enrollments_chart.items.map((item, index) => ({
                        label: item.label,
                        data: item.values
                    }))
                );
            }
            if (!byStatusChart) {
                byStatusChart = createChart(document.getElementById('masterstudy-doughnut-chart-by-status').getContext('2d'), 'doughnut');
            }
            if (!assignmentsChart && stats_data.assignments_addon) {
                assignmentsChart = createChart(document.getElementById('masterstudy-doughnut-chart-assignments').getContext('2d'), 'doughnut');
            }

            updateStatsBlock('.masterstudy-stats-block_new_courses', chartsData.new_courses);
            updateStatsBlock('.masterstudy-stats-block_enrollments', chartsData.total_enrollments);
            updateStatsBlock('.masterstudy-stats-block_certificates', chartsData.certificates);
            updateStatsBlock('.masterstudy-stats-block_new_students', chartsData.new_students);
            updateStatsBlock('.masterstudy-stats-block_new_assignments', chartsData.new_assignments);
            updateStatsBlock('.masterstudy-stats-block_new_lessons', chartsData.new_lessons);
            updateStatsBlock('.masterstudy-stats-block_new_quizzes', chartsData.new_quizzes);
            updateStatsBlock('.masterstudy-stats-block_new_groups_courses', chartsData.new_groups_courses);
            updateStatsBlock('.masterstudy-stats-block_new_trial_courses', chartsData.new_trial_courses);
            updateTotal('#enrollments-total', chartsData.total_enrollments);
            updateTotal('#unique-total', chartsData.unique_enrollments);
            updateTotal('#masterstudy-chart-total-by-status', chartsData.total_enrollments);
            updateTotal('#masterstudy-chart-total-assignments', chartsData.total_assignments);
            updateLineChart(enrollmentsChart, chartsData.enrollments_chart.period, chartsData.enrollments_chart.items);
            updateDoughnutChart(byStatusChart, chartsData.by_status);
            if ( stats_data.assignments_addon ) {
                updateDoughnutChart(assignmentsChart, chartsData.assignments);
            }

            hideLoaders('.masterstudy-analytics-engagement-page-line');
            hideLoaders('.masterstudy-analytics-engagement-page-stats');
            hideLoaders('.masterstudy-analytics-engagement-page-doughnut');
        }
    }

    function updateTable(currentRoute, reloadTable = false) {
        const dataSrc = function (json) {
            const pageInfo = $('#masterstudy-datatable-engagement').DataTable().page.info();
            const start = pageInfo.start;

            json.data = json.data.map((item, index) => {
                item.number = start + index + 1;
                return item;
            });

            return json.data;
        };

        const columnDefs = [
            { targets: engagement_page_data[currentRoute].length - 1, orderable: false },
            { targets: 0, width: '30px', orderable: false },
            {
                targets: engagement_page_data[currentRoute].length - 1,
                data: routes.engagementCoursesTable === currentRoute ? 'course_id' : 'student_id',
                render: function (data, type, row) {
                    const isEngagementCourse = routes.engagementCoursesTable === currentRoute;
                    const currentUrl = window.location.href;
                    const builderUrl = new URL(stats_data.user_account_url) + 'edit-course/' + data;
                    const courseUrl = new URL(stats_data.courses_page_url) + row.course_slug;
                    if ( !stats_data.is_user_account ) {
                        const newUrl = new URL(currentUrl);
                        const keysToDelete = isEngagementCourse ? ['user_id'] : ['course_id'];
                        const keysToSet = isEngagementCourse
                            ? { course_id: data }
                            : { user_id: data };
                        keysToDelete.forEach(key => newUrl.searchParams.delete(key));
                        Object.entries(keysToSet).forEach(([key, value]) => newUrl.searchParams.set(key, value));

                        return isEngagementCourse ? renderCourseButtons(newUrl, builderUrl, courseUrl) : renderReportButton(newUrl);
                    } else {
                        const baseUrl = new URL(stats_data.user_account_url);
                        const newPath = isEngagementCourse
                            ? `analytics/course/${data}`
                            : `analytics/student/${data}`;
                        baseUrl.pathname += `${newPath}`;

                        return isEngagementCourse ? renderCourseButtons(baseUrl, builderUrl, courseUrl) : renderReportButton(baseUrl);
                    }
                }
            }
        ]

        table = updateDataTable(
            table,
            '#masterstudy-datatable-engagement',
            ['.masterstudy-analytics-engagement-page-table'],
            currentRoute,
            engagement_page_data[currentRoute],
            dataSrc,
            columnDefs,
            reloadTable,
            false,
            false,
            [],
            currentSearchValue
        );
    }
})(jQuery);
