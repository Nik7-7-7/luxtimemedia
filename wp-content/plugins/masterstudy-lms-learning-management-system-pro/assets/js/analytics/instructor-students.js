(function($) {
    // Fetch data global variables
    let myStudentsTable = null;
    let currentSearchValue = null;

    document.addEventListener('datesUpdated', function(event) {
        updateTable(routes.usersMyStudentTable, true);
    });

    $(document).ready(function() {
        initializeDatepicker('#masterstudy-datepicker-instructor-students');
        updateTable(routes.usersMyStudentTable);

        $('.masterstudy-analytics-instructor-students-page__tabs .masterstudy-tabs__item').click(function() {
            const page = $(this).data('id');
            if ( page === 'revenue' ) {
                window.location.href = instructor_students_page_data.user_account_url;
            } else {
                window.location.href = `${instructor_students_page_data.user_account_url}${page}`;
            }
        });
    });

    // Listen for the custom search event
    document.addEventListener('intentTableSearch', function(event) {
        currentSearchValue = event.detail.searchValue;
        updateTable(routes.usersMyStudentTable, true); // Reload the table with the new search value
    });

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
            { targets: instructor_students_page_data[currentRoute].length - 1, orderable: false },
            {
                targets: instructor_students_page_data[currentRoute].length - 1,
                data: 'student_id',
                render: function (data, type, row) {
                    const currentUrl = window.location.href;
                    const newUrl = new URL(stats_data.user_account_url + `analytics/student/${data}`);
                    return renderReportButton(newUrl);
                }
            }
        ];

        let tableToUpdate = null;

        tableToUpdate = myStudentsTable;
        tableToUpdate = updateDataTable(
            tableToUpdate,
            `#masterstudy-datatable-${currentRoute}`,
            [`[data-chart-id="${currentRoute}-table"]`],
            currentRoute,
            instructor_students_page_data[currentRoute],
            dataSrc,
            columnDefs,
            reloadTable,
            false,
            false,
            [],
            currentSearchValue
        );

        myStudentsTable = tableToUpdate;
    }
})(jQuery);
