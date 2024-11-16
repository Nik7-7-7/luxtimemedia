(function($) {
    // Fetch data global variables
    let chartsData = null;

    // Fetch data
    fetchDataCharts();

    $(document).ready(function() {
        $('.masterstudy-analytics-short-report-page__tabs .masterstudy-tabs__item').click(function() {
            const period = $(this).data('id');
            $(this).addClass('masterstudy-tabs__item_active');
            $(this).siblings().removeClass('masterstudy-tabs__item_active');
            if (defaultDateRanges[period]) {
                updateDates(defaultDateRanges[period], null, false, false);
            }
        });
    });

    document.addEventListener('datesUpdated', function(event) {
        fetchDataCharts();
    });

    // Fetch data methods
    function fetchDataCharts() {
        if ( isDomReady ) {
            showLoaders('.masterstudy-analytics-short-report-page-stats ');
        }

        api.get( routes.shortReportCharts ).then(result => {
            if (result.error_code) {
                return
            }

            chartsData = {
                revenue: result.revenue,
                orders: result.orders,
                courses: result.courses,
                enrollments: result.enrollments,
                students: result.students,
                reviews: result.reviews,
                certificates: result.certificates,
                bundles: result.bundles,
            }

            updateCharts();
        })
    }

    // Update charts & table methods
    function updateCharts() {
        if (chartsData && isDomReady) {
            updateStatsBlock('.masterstudy-stats-block_revenue', chartsData.revenue, 'currency');
            updateStatsBlock('.masterstudy-stats-block_orders', chartsData.orders);
            updateStatsBlock('.masterstudy-stats-block_courses', chartsData.courses);
            updateStatsBlock('.masterstudy-stats-block_enrollments', chartsData.enrollments);
            updateStatsBlock('.masterstudy-stats-block_students', chartsData.students);
            updateStatsBlock('.masterstudy-stats-block_reviews', chartsData.reviews);
            updateStatsBlock('.masterstudy-stats-block_certificates_created', chartsData.certificates);
            updateStatsBlock('.masterstudy-stats-block_bundles', chartsData.bundles);

            hideLoaders('.masterstudy-analytics-short-report-page-stats');
        }
    }
})(jQuery);
