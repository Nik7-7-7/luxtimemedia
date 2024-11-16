(function($) {
    // Fetch data global variables
    let chartsData = null;
    const reviewsTableRoutes = [routes.reviewsPublishedTable, routes.reviewsPendingTable];
    const totalsArray = ['five', 'four', 'three', 'two', 'one'];
    let coursesChart = null;
    let reviewsTypeChart = null;
    let reviewersTable = null;
    let coursesTable = null;
    let reviewsTable = null;
    let currentSearchValue = null;

    // Fetch data
    fetchDataCharts();

    // Listen for the custom search event
    document.addEventListener('intentTableSearch', function(event) {
        currentSearchValue = event.detail.searchValue;
        const tab_id = $(event.detail.searchTarget)
            .parents('.masterstudy-analytics-reviews-page-table__header')
            .find('.masterstudy-tabs__item_active').data('id');
        if(tab_id === 'reviews-publish') updateTable(routes.reviewsPublishedTable, true);
        if(tab_id === 'reviews-pending') updateTable(routes.reviewsPendingTable, true);
    });

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
        updateTable(routes.reviewedCoursesTable, true);
        updateTable(routes.reviewersTable, true);
        $(`.masterstudy-tabs [data-id="${routes.reviewsPublishedTable}"]`).trigger('click');
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-reviews');

        $('.masterstudy-analytics-table__tabs .masterstudy-tabs__item').click(function() {
            const tabRoute = $(this).data('id');
            $(this).addClass('masterstudy-tabs__item_active');
            $(this).siblings().removeClass('masterstudy-tabs__item_active');
            updateTable(tabRoute, true);
        });

        $('.masterstudy-analytics-reviews-page__tabs .masterstudy-tabs__item').click(function() {
            const page = $(this).data('id');
            if ( page === 'revenue' ) {
                window.location.href = reviews_page_data.user_account_url;
            } else {
                window.location.href = `${reviews_page_data.user_account_url}${page}`;
            }
        });

        //Update data
        updateCharts();
        updateTable(routes.reviewedCoursesTable);
        updateTable(routes.reviewersTable);
        updateTable(routes.reviewsPublishedTable);
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-reviews-page-line');
        }

        api.get( routes.reviewsCharts ).then(result => {
            if (result.error_code) {
                return
            }
            chartsData = result;
            updateCharts();
        })
    }

    // Update charts & table methods
    function updateCharts() {
        if (chartsData && isDomReady) {
            if (!reviewsTypeChart) {
                reviewsTypeChart = createChart(
                    document.getElementById('masterstudy-line-chart-reviews-type').getContext('2d'),
                    'line',
                    chartsData.reviews_type_chart.period,
                    chartsData.reviews_type_chart.items.map((item, index) => ({
                        label: item.label,
                        data: item.values
                    }))
                );
            }

            if (!coursesChart) {
                coursesChart = createChart(document.getElementById('masterstudy-line-chart-reviews').getContext('2d'), 'line');
            }

            const correctOrder = chartsData.total_by_type ? [...chartsData.total_by_type].reverse() : [];

            if (correctOrder.length) {
                totalsArray.forEach((type, index) => {
                    updateTotal(`#reviews-totals-${type} .masterstudy-analytics-reviews-page-line__totals-value`, correctOrder[index]);
                });
            }
            updateTotal('#reviews-total', chartsData.total);
            updateLineChart(coursesChart, chartsData.courses_chart.period, chartsData.courses_chart.items);
            updateLineChart(reviewsTypeChart, chartsData.reviews_type_chart.period, chartsData.reviews_type_chart.items);

            hideLoaders('.masterstudy-analytics-reviews-page-line');
        }
    }

    function updateTable(currentRoute, reloadTable = false) {
        let selector = `#masterstudy-datatable-${currentRoute}`;
        let loaders = [`[data-chart-id="${currentRoute}-table"]`];
        let tableToUpdate = null;
        let pageData = reviews_page_data[currentRoute];
        let hidePagination = false;

        const dataSrc = function (json) {
            const pageInfo = $(`#masterstudy-datatable-${currentRoute}`).DataTable().page.info();
            const start = pageInfo?.start ?? 0;

            json.data = json.data.map((item, index) => {
                item.number = start + index + 1;
                return item;
            });

            if (reviewsTableRoutes.includes(currentRoute)) {
                updateTotal( '#reviews-table-total', json.recordsTotal);
            }

            return json.data;
        }

        let columnDefs = [
            { targets: 0,  width: '30px', orderable: false },
            { targets: 1, orderable: false },
            { targets: 2, orderable: false },
        ]

        // Add reviews tables
        if (reviewsTableRoutes.includes(currentRoute)) {
            loaders.push('[data-chart-id="reviews-table"]');

            selector = '#masterstudy-datatable-reviews-table';
            tableToUpdate = reviewsTable;
            pageData = reviews_page_data['reviews'];
            columnDefs = [
                { targets: 0, width: '30px', orderable: false },
                { targets: 3, orderable: false },
                { targets: reviews_page_data['reviews'].length - 1, orderable: false },
                {
                    targets: reviews_page_data['reviews'].length - 1,
                    data: 'review_id',
                    visible: stats_data.is_admin,
                    render: function (data, type, row) {
                        const protocol = window.location.protocol;
                        const host = window.location.host;
                        const path = '/wp-admin/post.php';
                        const newUrl = new URL(`${protocol}//${host}${path}`);
                        newUrl.searchParams.set('post', data);
                        newUrl.searchParams.set('action', 'edit');

                        return renderReportButton(newUrl.toString(), 'details');
                    }
                },
                {
                    targets: reviews_page_data['reviews'].length - 3,
                    data: 'rating',
                    render: function (data, type, row) {
                        return renderRating(data);
                    }
                }
            ];
        } else if (routes.reviewersTable === currentRoute) {
            tableToUpdate = reviewersTable;
            hidePagination = true;
        } else if (routes.reviewedCoursesTable === currentRoute) {
            tableToUpdate = coursesTable;
            hidePagination = true;
        }

        tableToUpdate = updateDataTable(
            tableToUpdate,
            selector,
            loaders,
            currentRoute,
            pageData,
            dataSrc,
            columnDefs,
            reloadTable,
            hidePagination,
            false,
            [],
            currentSearchValue
        );

        if (reviewsTableRoutes.includes(currentRoute)) {
            reviewsTable = tableToUpdate;
        } else if (routes.reviewersTable === currentRoute) {
            reviewersTable = tableToUpdate;
        } else if (routes.reviewedCoursesTable === currentRoute) {
            coursesTable = tableToUpdate;
        }
    }
})(jQuery);
