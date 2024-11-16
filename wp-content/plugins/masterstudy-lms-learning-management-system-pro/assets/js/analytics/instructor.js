(function($) {
    // Fetch data global variables
    let chartsData = null;
    let revenueChart = null;
    let enrollmentsChart = null;
    let coursesTable = null;
    let membershipTable = null;
    let table = null;
    let currentSearchValue = null;

    // Fetch data
    fetchDataCharts();

    // Listen for the custom search event
    document.addEventListener('intentTableSearch', function(event) {
        currentSearchValue = event.detail.searchValue;
        updateTable(routes.instructorCoursesTable, true); // Reload the table with the new search value
    });

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
        updateTable(routes.instructorCoursesTable, true);
        if (instructor_page_data.is_membership_active) {
            updateTable(routes.instructorMembershipTable, true);
        }
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-instructor');

        // Update data
        updateCharts();
        updateTable(routes.instructorCoursesTable);
        if (instructor_page_data.is_membership_active) {
            updateTable(routes.instructorMembershipTable);
        }
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-instructor-page-line');
        }

        api.get( routes.instructorCharts ).then(result => {
            if (result.error_code) {
                return
            }

            chartsData = {
                total_revenue: result.total_revenue,
                total_enrollments: result.total_enrollments,
                unique_enrollments: result.unique_enrollments,
                revenue_chart: {
                    period: result.earnings?.period,
                    items: [
                        { label: instructor_page_data.titles.revenue_chart, values: result.earnings?.values },
                    ]
                },
                enrollments_chart: {
                    period: result.enrollments?.period,
                    items: [
                        { label: instructor_page_data.titles.enrollments_chart.total, values: result.enrollments?.all },
                        { label: instructor_page_data.titles.enrollments_chart.unique, values: result.enrollments?.unique },
                    ]
                },
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

            updateTotal('#revenue-total', chartsData.total_revenue, 'currency');
            updateTotal('#enrollments-total', chartsData.total_enrollments);
            updateTotal('#unique-total', chartsData.unique_enrollments);
            updateLineChart(revenueChart, chartsData.revenue_chart.period, chartsData.revenue_chart.items);
            updateLineChart(enrollmentsChart, chartsData.enrollments_chart.period, chartsData.enrollments_chart.items);

            hideLoaders('.masterstudy-analytics-instructor-page-line');
        }
    }

    function updateTable(currentRoute, reloadTable = false) {
        const routeKey = currentRoute.split('/').pop();
        const dataSrc = function (json) {
            const pageInfo = $(`#masterstudy-datatable-${routeKey}`).DataTable().page.info();
            const start = pageInfo.start;

            json.data = json.data.map((item, index) => {
                item.number = start + index + 1;

                if (item.hasOwnProperty('revenue')) {
                    item.revenue = formatCurrency(item.revenue);
                }

                if (item.hasOwnProperty('price')) {
                    item.price = formatCurrency(item.price);
                }

                return item;
            });

            return json.data;
        }

        let columnDefs = [{ targets: 0, width: '30px', orderable: false }];

        if (routes.instructorCoursesTable === currentRoute) {
            columnDefs = [
                ...columnDefs,
                { targets: instructor_page_data[routeKey].length - 1, orderable: false },
                {
                    targets: instructor_page_data.courses.length - 1,
                    data: 'course_id',
                    render: function (data, type, row) {
                        const currentUrl = window.location.href;
                        const builderUrl = new URL(stats_data.user_account_url) + 'edit-course/' + data;
                        const courseUrl = new URL(stats_data.courses_page_url) + row.course_slug;
                        const newUrl = new URL(currentUrl);
                        const keysToDelete = ['role', 'user_id'];
                        keysToDelete.forEach(param => newUrl.searchParams.delete(param));
                        newUrl.searchParams.set('course_id', data);

                        return renderCourseButtons(newUrl, builderUrl, courseUrl);
                    }
                }
            ];
        }

        let tableToUpdate = null;

        if (routes.instructorCoursesTable === currentRoute) {
            tableToUpdate = coursesTable;
        } else if (routes.instructorMembershipTable === currentRoute) {
            tableToUpdate = membershipTable;
        }

        tableToUpdate = updateDataTable(
            tableToUpdate,
            `#masterstudy-datatable-${routeKey}`,
            [`[data-chart-id="${routeKey}-table"]`],
            currentRoute,
            instructor_page_data[routeKey],
            dataSrc,
            columnDefs,
            reloadTable,
            false,
            false,
            [],
            currentSearchValue
        );

        if (routes.instructorCoursesTable === currentRoute) {
            coursesTable = tableToUpdate;
        } else if (routes.instructorMembershipTable === currentRoute) {
            membershipTable = tableToUpdate;
        }
    }
})(jQuery);
