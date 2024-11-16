(function ($) {
    $(document).ready(function () {
        const table     = $('.masterstudy-table');
        const tableBody = table.find('.masterstudy-tbody');

        let per_page  = 10, 
            search = '', 
            status = '',
            sortby = '',
            sort   = '',
            page   = 1;

        const pagination = new MasterstudyPagination({
            visibleNumber: 3, 
            perPageLimit: per_page, 
            dataListContainer: '.masterstudy-tbody',
            dataItemElementsClass: '.masterstudy-table__item',
            dataItemExcludeClass: 'masterstudy-table__item--hidden',
            dataItemDisplayCss: 'flex'
        });
        
        pagination.onPageChange(function(page, isPageLoadedBefore) {
            pageItemsVisibility(page);
            if (1 !== page) {
                if (!isPageLoadedBefore) {
                    table.find('.masterstudy-tfooter').addClass('masterstudy-tfooter--hidden');
                    fetchData({per_page, search, status, page}, false);
                }
            }
        });
        // on page load.
        fetchData({per_page, search, status, page});

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
            fetchData({per_page, search, status, page: 1}, true);
        });

        function fetchData(params = {}, isClearData = true) {
            if (isClearData) {
                clearTableData();
            }

            const queryString = new URLSearchParams(params).toString();
            const apiUrl = `${ms_lms_resturl}/assignments/?${queryString}`;
            
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
                    const data = JSON.parse(response);
                    if(response) {
                        loader(true);
                        updateUI(JSON.parse(response), isClearData);
                    }
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
                    addDataToTable(order, assignments, data.page, data.per_page);
                });

                sortTableItems(sort, sortby);

                console.log(sort, sortby)
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

        function addDataToTable(order, data, page, per_page) {
            const tableItem = tableBody.find('.masterstudy-table__item:first').clone();
            tableItem.removeClass('masterstudy-table__item--hidden');
            tableItem.find('.masterstudy-tcell__data').each(function(i, cell) {
                const key   = $(cell).data('key');
                const value = (data[key] || data[key] === 0) ? data[key] : '';

                switch(key) {
                    case 'courses':
                        const coursesHtml = relatedCourses(value);
                        if (coursesHtml) {
                            $(cell).find('.masterstudy-table__list-no-course').addClass('hidden');
                            $(cell).append(coursesHtml);
                        } else {
                            $(cell).find('.masterstudy-table__list-no-course').removeClass('hidden');
                        }
                    case 'progress_view_link':
                        $(cell).find('[data-id="manage-students-view-progress"]').attr('href', value);
                        break;
                    case 'more_link':
                        $(cell).find('[data-id="more-link"]').attr('href', value);
                        break;
                    default:
                        $(cell).text(value);
                        $(cell).attr('data-value', value);
                        break;
                }
            });
            tableItem.attr('data-page', page);
            tableItem.attr('data-initial-order', (order+1)+((page-1)*per_page));

            let inserted   = false;
            const nextItem = tableBody.find(`.masterstudy-table__item[data-page="${page+1}"]:first`);

            if ( nextItem && nextItem.length > 0 ) {
                nextItem.css({display: 'none'})
                tableItem.insertBefore(nextItem);
                inserted = true;
            }

            if( !inserted ) {
                tableBody.append(tableItem);
            }
        }

        function relatedCourses(courses) {
            let fragment = document.createDocumentFragment();
            if(courses.length > 0) {
                courses.forEach(function(course){
                    const listItem   = document.createElement("li");
                    const courseLink = document.createElement("a");
                    listItem.setAttribute("data-course-id", course.id);
                    courseLink.setAttribute("href", course.link);
                    courseLink.textContent = course.title;
                    listItem.appendChild(courseLink);
                    fragment.appendChild(listItem);
                });
            } else {
                fragment = '';
            }
            return fragment;
        }

        function pageItemsVisibility(page) {
            const tableItems = tableBody.find('.masterstudy-table__item');
            tableItems.each(function() {
                const itemPage = $(this).data('page');
                if (itemPage ) {
                    if (page == itemPage ) {
                        $(this).css({display: 'flex'})
                    } else{
                        $(this).css({display: 'none'})
                    }
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

        document.addEventListener('msSortIndicatorEvent', function(event) {
            sortby = event.detail.indicator.parents('.masterstudy-tcell__header').data('sort');
            sort   = event.detail.sortOrder;
            sortTableItems( sort, sortby );
        });

        function sortTableItems( sortOrder, sortby ) {
            const sortingItems = tableBody.find( '.masterstudy-table__item' ).not('.masterstudy-table__item--hidden');
            
            if ( !sortOrder || !sortby ) {
                return;
            }
            
            sortingItems.sort( function( a, b ) {
                let aValue = $(a).find('.masterstudy-tcell [data-key="' + sortby + '"]').data('value');
                let bValue = $(b).find('.masterstudy-tcell [data-key="' + sortby + '"]').data('value');

                    // Handle empty values
                if (aValue === '' && bValue === '') {
                    return 0;
                }

                if (aValue === '' || bValue === '') {
                    return sortOrder === 'asc' ? (aValue === '' ? -1 : 1) : (aValue === '' ? 1 : -1);
                }

                let sorted = 0;

                if ( sortOrder === 'none' ) {
                    sorted =  $(a).data( 'initial-order' ) - $(b).data( 'initial-order' );
                } else {
                    const isDate   = !isNaN(Date.parse(aValue)) && !isNaN(Date.parse(bValue));
                    const isNumber = !isNaN(parseFloat(aValue)) && isFinite(aValue);

                    if (!isDate && !isNumber) {
                        aValue = aValue.toLowerCase();
                        bValue = bValue.toLowerCase();
                        sorted = ( sortOrder === 'asc' ) ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                    } else if (isDate) {
                        aValue = new Date(aValue);
                        bValue = new Date(bValue);
                        sorted = ( sortOrder === 'asc' ) ?  aValue - bValue : bValue - aValue;
                    } else if (isNumber) {
                        aValue = parseFloat(aValue);
                        bValue = parseFloat(bValue);
                        sorted = ( sortOrder === 'asc' ) ?  aValue - bValue : bValue - aValue;
                    }
                }
                return sorted;
            });
            
            const hiddenItems = tableBody.find('.masterstudy-table__item--hidden');
            tableBody.empty().append( sortingItems );
            tableBody.prepend( hiddenItems );
        }
    });

})(jQuery);