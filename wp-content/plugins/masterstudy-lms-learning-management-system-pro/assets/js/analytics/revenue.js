(function($) {
    // Fetch data global variables
    let chartsData = null;
    let payoutsData = null;
    let revenueChart = null;
    let byProductChart = null;
    let byStudentsChart = null;
    let payoutsChart = null;
    let table = null;
    // Store the search value
    let currentSearchValue = null;

    // Fetch data
    fetchDataCharts();
    fetchPayouts();

    // Listen for the custom search event
    document.addEventListener('intentTableSearch', function(event) {
        currentSearchValue = event.detail.searchValue;
        const tabRoute = $(event.detail.searchTarget)
            .parents('.masterstudy-analytics-revenue-page-table__header')
            .find('.masterstudy-tabs__item.masterstudy-tabs__item_active')
            .data('id');

        updateTable(tabRoute, true); // Reload the table with the new search value
    });

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
        fetchPayouts();
        $(`.masterstudy-tabs [data-id="${routes.revenueCoursesTable}"]`).trigger('click');
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-revenue');

        $('.masterstudy-analytics-table__tabs .masterstudy-tabs__item').click(function() {
            const tabRoute = $(this).data('id');
            $(this).parents('.masterstudy-analytics-revenue-page-table__wrapper')
                .find('.masterstudy-analytics-revenue-page-table__search')
                .attr('placeholder', revenue_page_data.search_placeholders[tabRoute])
            $(this).addClass('masterstudy-tabs__item_active');
            $(this).siblings().removeClass('masterstudy-tabs__item_active');
            updateTable(tabRoute, true);
        });

        $('.masterstudy-analytics-revenue-page__tabs .masterstudy-tabs__item').click(function() {
            const page = $(this).data('id');
            if ( page === 'revenue' ) {
                window.location.href = revenue_page_data.user_account_url;
            } else {
                window.location.href = `${revenue_page_data.user_account_url}${page}`;
            }
        });

        //Update data
        updateCharts();
        if (stats_data.payouts_addon && (stats_data.instructors_payouts && stats_data.is_user_account) || !stats_data.is_user_account) {
            updatePayouts();
        }
        updateTable(routes.revenueCoursesTable);
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-revenue-page-line');
            showLoaders('.masterstudy-analytics-revenue-page-stats');
            showLoaders('[data-chart-id="by-product"');
            showLoaders('[data-chart-id="by-students"');
        }

        api.get( routes.revenueCharts ).then(result => {
            if (result.error_code) {
                return
            }

            chartsData = {
                total_revenue: result.total_revenue,
                courses_total: result.courses_total,
                bundles_total: result.bundles_total,
                orders_count: result.orders_count,
                memberships_count: result.memberships_count,
                revenue: {
                    period: result.earnings?.period,
                    items: [
                        { label: revenue_page_data.titles.revenue, values: result.earnings?.values },
                    ]
                },
                by_product: {
                    labels: revenue_page_data.titles.by_product,
                    values: [result.courses_total, result.bundles_total],
                    percents: getPercentesByValues([result.courses_total, result.bundles_total]),
                },
                by_students: {
                    labels: revenue_page_data.titles.by_students,
                    values: [result.existing_students_total, result.new_students_total],
                    percents: getPercentesByValues([result.existing_students_total, result.new_students_total]),
                }
            }

            updateCharts();
        })
    }

    function fetchPayouts() {
        if ( isDomReady ) {
            showLoaders('[data-chart-id="payouts"');
        }

        api.get( routes.payoutsChart ).then(result => {
            if (result.error_code) {
                return
            }

            payoutsData = {
                total: result.instructor_revenue + result.admin_comission,
                labels: revenue_page_data.titles.payouts,
                values: [result.instructor_revenue, result.admin_comission],
                percents: getPercentesByValues([result.instructor_revenue, result.admin_comission]),
            };

            if (stats_data.payouts_addon && (stats_data.instructors_payouts && stats_data.is_user_account) || !stats_data.is_user_account) {
                updatePayouts();
            }
        })
    }

    // Update charts & table methods
    function updateCharts() {
        if (chartsData && isDomReady) {
            if (!revenueChart) {
                revenueChart = createChart(document.getElementById('masterstudy-line-chart-revenue').getContext('2d'), 'line', [], [], true);
            }
            if (!byProductChart && stats_data.bundle_addon) {
                byProductChart = createChart(document.getElementById('masterstudy-doughnut-chart-by-product').getContext('2d'), 'doughnut', [], [], true);
            }
            if (!byStudentsChart) {
                byStudentsChart = createChart(document.getElementById('masterstudy-doughnut-chart-by-students').getContext('2d'), 'doughnut', [], [], true);
            }

            updateStatsBlock('.masterstudy-stats-block_revenue', chartsData.total_revenue, 'currency');
            updateStatsBlock('.masterstudy-stats-block_admin_commissions', chartsData.admin_commissions, 'currency');
            updateStatsBlock('.masterstudy-stats-block_withdraws', chartsData.withdraws, 'currency');
            updateStatsBlock('.masterstudy-stats-block_courses', chartsData.courses_total, 'currency');
            updateStatsBlock('.masterstudy-stats-block_bundles', chartsData.bundles_total, 'currency');
            updateStatsBlock('.masterstudy-stats-block_orders', chartsData.orders_count);
            if (!stats_data.is_user_account) {
                updateStatsBlock('.masterstudy-stats-block_memberships', chartsData.memberships_count);
            }
            updateTotal('#revenue-total', chartsData.total_revenue, 'currency');
            updateTotal('#masterstudy-chart-total-by-product', chartsData.total_revenue, 'currency');
            updateTotal('#masterstudy-chart-total-by-students', chartsData.total_revenue, 'currency');
            updateDoughnutChart(byStudentsChart, chartsData.by_students, 'currency');
            updateLineChart(revenueChart, chartsData.revenue.period, chartsData.revenue.items);
            if (stats_data.bundle_addon) {
                updateDoughnutChart(byProductChart, chartsData.by_product, 'currency');
                hideLoaders('[data-chart-id="by-product"');
            }
            hideLoaders('[data-chart-id="by-students"');
            hideLoaders('.masterstudy-analytics-revenue-page-line');
            hideLoaders('.masterstudy-analytics-revenue-page-stats');
        }
    }

    function updatePayouts() {
        if (payoutsData && isDomReady) {
            if (!payoutsChart) {
                payoutsChart = createChart(document.getElementById('masterstudy-doughnut-chart-payouts').getContext('2d'), 'doughnut', [], [], true);
            }
            updateTotal('#masterstudy-chart-total-payouts', payoutsData.total, 'currency');
            updateDoughnutChart(payoutsChart, {
                labels: payoutsData.labels,
                values: payoutsData.values,
                percents: payoutsData.percents,
            }, 'currency');

            hideLoaders('[data-chart-id="payouts"');
        }
    }

    function updateTable(currentRoute, reloadTable = false) {
        const dataSrc = function (json) {
            const pageInfo = $('#masterstudy-datatable-revenue').DataTable().page.info();
            const start = pageInfo.start;

            json.data = json.data.map((item, index) => {
                item.number = start + index + 1;

                if (item.hasOwnProperty('revenue')) {
                    item.revenue = formatCurrency(item.revenue);
                }

                return item;
            });

            return json.data;
        }

        let columnDefs = [{ targets: 0, width: '30px', orderable: false }];

        if (currentRoute === routes.revenueCoursesTable || currentRoute === routes.revenueStudentsTable) {
            let columnToRender;
            if (currentRoute === routes.revenueCoursesTable) {
                columnToRender = 'course_id';
            } else if (currentRoute === routes.revenueStudentsTable) {
                columnToRender = 'student_id';
            }

            columnDefs = [
                { targets: revenue_page_data[currentRoute].length - 1, orderable: false },
                { targets: 0, width: '30px', orderable: false },
                {
                    targets: revenue_page_data[currentRoute].length - 1,
                    data: columnToRender,
                    render: function (data, type, row) {
                        const isRevenueCourse = routes.revenueCoursesTable === currentRoute;
                        const currentUrl = window.location.href;
                        const builderUrl = new URL(stats_data.user_account_url) + 'edit-course/' + data;
                        const courseUrl = new URL(stats_data.courses_page_url) + row.course_slug;
                        if ( !stats_data.is_user_account ) {
                            const newUrl = new URL(currentUrl);
                            const keysToDelete = isRevenueCourse ? ['user_id'] : ['course_id'];
                            const keysToSet = isRevenueCourse
                                ? { course_id: data }
                                : { user_id: data };
                            keysToDelete.forEach(key => newUrl.searchParams.delete(key));
                            Object.entries(keysToSet).forEach(([key, value]) => newUrl.searchParams.set(key, value));

                            return isRevenueCourse ? renderCourseButtons(newUrl, builderUrl, courseUrl) : renderReportButton(newUrl);
                        } else {
                            const baseUrl = new URL(stats_data.user_account_url);
                            const newPath = isRevenueCourse
                                ? `analytics/course/${data}`
                                : `analytics/student/${data}`;
                            baseUrl.pathname += `${newPath}`;

                            return isRevenueCourse ? renderCourseButtons(baseUrl, builderUrl, courseUrl) : renderReportButton(baseUrl);
                        }
                    }
                }
            ];
        }

        table = updateDataTable(
            table,
            '#masterstudy-datatable-revenue',
            ['.masterstudy-analytics-revenue-page-table'],
            currentRoute,
            revenue_page_data[currentRoute],
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
