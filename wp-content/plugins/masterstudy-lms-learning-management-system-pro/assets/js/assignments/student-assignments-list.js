(function($){
    $(document).ready(function() {
        const table     = $('.masterstudy-table');
        const tableBody = table.find('.masterstudy-tbody');
        const assignment_id = stm_lms_assignment.assignment_id;

        let per_page  = 10; 
        let search = ''; 
        let status = ''; 
        let page = 1;
        let sortby = '';
        let sort_order = '';

        const pagination = new MasterstudyPagination({
            visibleNumber: 3, 
            perPageLimit: per_page, 
            dataListContainer: '.masterstudy-tbody',
            dataItemElementsClass: '.masterstudy-table__item',
            dataItemExcludeClass: 'masterstudy-table__item--hidden',
            dataItemDisplayCss: 'flex'
        });
        
        pagination.onPageChange(function(page, isPageLoadedBefore) {
            if (!isPageLoadedBefore && 1 !== page) {
                table.find('.masterstudy-tfooter').addClass('masterstudy-tfooter--hidden');
                fetchData({assignment_id, per_page, s: search, status, page, sortby, sort_order}, false);
            }
        });
        fetchData({assignment_id, per_page, s: search, status, page, sortby, sort_order});

        document.addEventListener('msSortIndicatorEvent', function(event) {
            sort_order = event.detail.sortOrder;
            sortby     = event.detail.indicator.parents('.masterstudy-tcell__header').data('sort');
            table.find('.masterstudy-tfooter').addClass('masterstudy-tfooter--hidden');
            fetchData({assignment_id, per_page, s: search, status, page, sortby, sort_order}, true);
        });

        document.addEventListener('msfieldEvent', function(event) {
            const fieldValue = event.detail.value;
            table.find('.masterstudy-tfooter').addClass('masterstudy-tfooter--hidden');
            switch ( event.detail.name ) {
                case 's': 
                    search = fieldValue ? fieldValue : '';
                    $('.masterstudy-select__clear').click();
                    $('.masterstudy-select').removeClass('masterstudy-select_open');
                    break;
                case 'per_page': 
                    per_page = fieldValue ? fieldValue : 10;
                    break;
                case 'status': 
                    status = fieldValue ? fieldValue : '';
                    break;                
            }
            fetchData({assignment_id, per_page, s: search, status, page: 1, sortby, sort_order}, true);
        });

        function addDataToTable(order, data) {
            const tableItem = tableBody.find('.masterstudy-table__item:first').clone();
            tableItem.removeClass('masterstudy-table__item--hidden');
            tableItem.find('.masterstudy-tcell__data').each(function(i, cell) {
                const key   = $(cell).data('key');
                const value = (data[key] || data[key] === 0) ? data[key] : '';

                switch(key) {
                    case 'course':
                        if (value.title) {
                            $(cell).parent().find('.masterstudy-table__list-no-course').addClass('hidden');
                            $(cell).text(value.title);
                            $(cell).attr('href', value.link);
                        } else {
                            $(cell).find('.masterstudy-table__list-no-course').removeClass('hidden');
                        }
                    case 'review_link':
                        $(cell).find('[data-id="student-assignment-review"]').attr('href', value);
                        break;
                    case 'status':
                        const icons = {
                            pending: 'far fa-clock',
                            passed: 'fa fa-check',
                            not_passed: 'fa fa-times',
                        };
                        $(cell).parent().find('i').addClass(icons[value.slug]);
                        $(cell).text(value.title);
                        break;
                    default:
                        $(cell).text(value);
                        $(cell).attr('data-value', value);
                        break;
                }
            });
            tableItem.attr( 'data-initial-order', order );
            tableBody.append(tableItem);
        }

        function fetchData(params = {}, isClearData = true) {
            if (isClearData) {
                clearTableData();
            }
            const queryString = new URLSearchParams(params).toString();
            const apiUrl = `${ms_lms_resturl}/student-assignments/?${queryString}`;

            loader();
        
            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': ms_lms_nonce ,    
                    'Content-Type': 'application/json',
                },
            }).then(response => {
                if (response.ok) {
                    return response.json();
                }
            }).then(response => {
                setTimeout(function(){
                    loader(true);
                    updateUI(JSON.parse(response), isClearData);
                }, 1500);
            }).catch(error => {
                throw error;
            });
        }

        function updateUI(data, isClearData) {
            if (data) {
                if (isClearData) {
                    clearTableData();
                }
                notFound(data.max_pages);
                pagination.paginate(data.max_pages, data.per_page, isClearData);

                $.each(data.assignments, function(order, assignments) {
                    addDataToTable(order, assignments);
                });
            }
        }

        function clearTableData() {
            const tableItems = tableBody.find('.masterstudy-table__item');
            $.each(tableItems, function(i, item){
                if (1 < i) {
                    $(item).remove();
                }
            });
        }

        function loader(isToHide = false) {
            if (isToHide) {
                tableBody.find('.masterstudy-loader').remove();
            } else {
                const prevLoader = tableBody.find('.masterstudy-loader');
                if (prevLoader.length < 1) {
                    const loader = $('.masterstudy-loader').clone();
                    loader.css({display: 'block'});
                    tableBody.append(loader);
                }
            }
        }

        function notFound(maxPages) {
            tableBody.find('.masterstudy-table__item.not-founded').remove();
            if (0 === maxPages) {
                const tableItem = tableBody.find('.masterstudy-table__item').eq(1).clone();
                tableItem.removeClass('masterstudy-table__item--hidden');
                tableItem.addClass('not-founded');
                tableBody.append(tableItem);
            }
            if(maxPages <= 1) {
                table.find('.masterstudy-tfooter').addClass('masterstudy-tfooter--hidden');
            } else {
                table.find('.masterstudy-tfooter').removeClass('masterstudy-tfooter--hidden');
            }
        }
    });
})(jQuery);