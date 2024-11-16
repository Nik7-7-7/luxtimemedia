(function($) {
    $(document).ready(function() {
        const table     = $('.masterstudy-instructor-certificates');
        const tableBody = table.find('.masterstudy-instructor-certificates__list');
        
        if (tableBody.length) {
            let per_page = 10, 
                search = '', 
                category = '', 
                instructor = 0, 
                page = 1;

            const pagination = new MasterstudyPagination({
                visibleNumber: 3, 
                perPageLimit: per_page, 
                dataListContainer: '.masterstudy-instructor-certificates__list',
                dataItemElementsClass: '.masterstudy-instructor-certificates__item',
                dataItemExcludeClass: 'masterstudy-instructor-certificates__item--hidden',
                dataItemDisplayCss: 'flex'
            });
            
            pagination.onPageChange(function(page, isPageLoadedBefore) {
                hidePageItems(page);
                if (1 !== page) {
                    if (!isPageLoadedBefore) {
                        fetchData({per_page, s: search, by_category: category, by_instructor: instructor, page}, false);
                    }
                }
            });
            // on page load.
            fetchData({per_page, s: search, by_category: category, by_instructor: instructor, page}, true);

            document.addEventListener('msfieldEvent', function(event) {
                const fieldValue = event.detail.value;
                tableBody.find('.masterstudy-instructor-certificates__list-not-found').addClass('masterstudy-instructor-certificates__item--hidden');
                switch ( event.detail.name ) {
                    case 's': 
                        search = fieldValue ? fieldValue : '';
                        $('.masterstudy-select__clear').click();
                        $('.masterstudy-select').removeClass('masterstudy-select_open');
                        break;
                    case 'per_page':
                        per_page = fieldValue ? fieldValue : 10;
                        break;
                    case 'by_category':
                        category = fieldValue ? fieldValue : '';
                        break;                
                    case 'by_instructor':
                        instructor = fieldValue ? fieldValue : 0;
                        break;                
                }
                fetchData({per_page, s: search, by_category: category, by_instructor: instructor, page}, true);
            });

            function fetchData(params = {}, isClearData = true) {
                if (isClearData) {
                    clearTableData();
                }
                const queryString = new URLSearchParams(params).toString();
                const apiUrl = `${ms_lms_resturl}/certificates/?${queryString}`;
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
                    
                    // hidePageItems(data.page);

                    $.each(data.certificates, function(order, certificate) {
                        addDataToTable(order, certificate, data.page, data.per_page);
                    });
                }
            }
            function clearTableData() {
                const tableItems = tableBody.find('.masterstudy-instructor-certificates__item');
                $.each(tableItems, function(i, item){
                    if (0 < i) {
                        $(item).remove();
                    }
                });
            }
            function notFound(maxPages) {
                const notFounded = tableBody.find('.masterstudy-instructor-certificates__list-not-found');
                if (0 === maxPages) {
                    notFounded.addClass('masterstudy-instructor-certificates__item--hidden');
                }
                if(maxPages <= 1) {
                    table.find('.masterstudy-instructor-certificates__content-bottom').addClass('hidden');
                    if(maxPages < 1) {
                        notFounded.removeClass('masterstudy-instructor-certificates__item--hidden');
                    }
                } else {
                    table.find('.masterstudy-instructor-certificates__content-bottom').removeClass('hidden');
                    notFounded.addClass('masterstudy-instructor-certificates__item--hidden');
                }
            }
            function addDataToTable(order, data, page, per_page) {
                const tableItem = tableBody.find('.masterstudy-instructor-certificates__item:first').clone();
                tableItem.removeClass('masterstudy-instructor-certificates__item--hidden');
                tableItem.find('.masterstudy-instructor-certificates__data').each(function(i, cell) {
                    const key   = $(cell).data('key');
                    const value = (data[key] || data[key] === 0) ? data[key] : '';
                    switch(key) {
                        case 'image':
                            const orientation = data['orientation'] || 'landscape';
                            $(cell).addClass(`masterstudy-instructor-certificates__item-image_${orientation}`);
                            $(cell).find('img').attr('src', value);
                            $(cell).html(`<img src="${value}"/>`);
                            break;
                        case 'category_name':
                            const isDefault = data['is_default'] || false;
                            if (!isDefault && value) {
                                $(cell).text(value);
                            }
                            if (!isDefault && !value) {
                                $(cell).text('-');
                            }
                            $(cell).attr('data-value', value);
                            break;
                        case 'delete_id':
                            $(cell).attr('data-value', data['id'] || 0);
                            break;
                        case 'edit_link':
                            $(cell).find('[data-id="masterstudy-instructor-certificates-edit"]').attr('href', value);
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
                const nextItem = tableBody.find(`.masterstudy-instructor-certificates__item[data-page="${page+1}"]:first`);

                if ( nextItem && nextItem.length > 0 ) {
                    nextItem.css({display: 'none'})
                    tableItem.insertBefore(nextItem);
                    inserted = true;
                }

                if( !inserted ) {
                    tableBody.append(tableItem);
                }
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
                        table.find('.masterstudy-instructor-certificates__content-bottom').addClass('hidden');
                    }
                }
            }

            function hidePageItems(page) {
                const tableItems = tableBody.find('.masterstudy-instructor-certificates__item');
                tableItems.each(function() {
                    const itemPage = $(this).data('page');
                    if (itemPage && page != itemPage ) {
                        $(this).css({display: 'none'})
                    }
                });
            }

            const alertPopup = $("[data-id='masterstudy-instructor-certificates-alert']");
            let certificate_id = ''; 

            alertPopup.css('display', 'none');

            $('body').on('click', '.masterstudy-instructor-certificates__delete', function() {
                alertPopup.css('display', 'flex');
                alertPopup.addClass('masterstudy-alert_open');
                certificate_id = $(this).data('value');
            });
            alertPopup.find("[data-id='submit']").click(function(e) {
                e.preventDefault();

                const pageOptions = pagination.getPageOptions();
                const apiUrl = `${ms_lms_resturl}/certificates/${certificate_id}`;
                page = pageOptions.page || 1;

                tableBody.find(`[data-page="${page}"]`).remove();
                alertPopup.removeClass('masterstudy-alert_open');

                fetch(apiUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': ms_lms_nonce ,
                        'Content-Type': 'application/json',
                    },
                }).then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                }).then(response => {
                    if ('ok' === response.status) {
                        fetchData({per_page, s: search, by_category: category, by_instructor: instructor, page}, false);
                    }
                }).catch(error => {
                    throw error;
                });
            })
            alertPopup.find("[data-id='cancel']").click(closeAlertPopup);
            alertPopup.find('.masterstudy-alert__header-close').click(closeAlertPopup);

            function closeAlertPopup(e) {
                e.preventDefault();
                alertPopup.removeClass('masterstudy-alert_open');
            }
        }
    });
})(jQuery);