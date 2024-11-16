const api = new MasterstudyApiProvider( 'analytics/' );
const currentUrl = window.location.href;
const userAccountDashboardPage = currentUrl === stats_data.user_account_url;
const user_id = currentUrl.includes(stats_data.user_account_url) ? currentUrl.split('/').filter(Boolean).pop() : getQueryParam('user_id');
const course_id = currentUrl.includes(stats_data.user_account_url) ? currentUrl.split('/').filter(Boolean).pop() : getQueryParam('course_id');
const routes = {
    revenueCharts: 'revenue',
    payoutsChart: 'revenue/payouts',
    revenueCoursesTable: 'revenue/courses',
    revenueGroupsTable: 'revenue/groups',
    revenueBundlesTable: 'revenue/bundles',
    revenueStudentsTable: 'revenue/students',
    engagementCharts: 'engagement',
    engagementCoursesTable: 'engagement/courses',
    engagementStudentsTable: 'engagement/students',
    usersStudentTable: 'students',
    usersMyStudentTable: 'instructor-students',
    usersInstructorTable: 'instructors',
    usersCharts: 'users',
    reviewsCharts: 'reviews-charts',
    reviewedCoursesTable: 'reviews-courses',
    reviewersTable: 'reviews-users',
    reviewsPublishedTable: 'reviews-publish',
    reviewsPendingTable: 'reviews-pending',
    studentCharts: `student/${user_id}/data`,
    studentCoursesTable: `student/${user_id}/courses`,
    studentMembershipTable: `student/${user_id}/membership`,
    instructorCharts: `instructor/${user_id}/data`,
    instructorCoursesTable: `instructor/${user_id}/courses`,
    instructorMembershipTable: `instructor/${user_id}/membership`,
    courseCharts: `course/${course_id}/data`,
    courseLessonsTable: `course/${course_id}/lessons`,
    courseLessonsUsersTable: `course/${course_id}/lessons-by-users`,
    shortReportCharts: 'instructor/short-report',
};

let pageTitle = false;
const courseTitleElement = document.querySelector('.masterstudy-analytics-course-page__title');
const userNameElement = document.querySelector('.masterstudy-analytics-student-page__name') || document.querySelector('.masterstudy-analytics-instructor-page__name');
const userRoleElement = document.querySelector('.masterstudy-analytics-student-page__role') || document.querySelector('.masterstudy-analytics-instructor-page__role');

if (courseTitleElement) {
    pageTitle = courseTitleElement.textContent;
} else if (userNameElement) {
    pageTitle = userNameElement.textContent;
} else if (userRoleElement) {
    pageTitle = userRoleElement.textContent;
}

if (pageTitle) {
    document.title = pageTitle;
}

let selectedSettingsIds = [];
const defaultDateRanges = getDefaultDateRanges();
let isDomReady = false;
let storedPeriodKey = localStorage.getItem('AnalyticsSelectedPeriodKey');
let selectedPeriod;

if (storedPeriodKey && defaultDateRanges[storedPeriodKey] && !userAccountDashboardPage) {
    selectedPeriod = defaultDateRanges[storedPeriodKey];
} else {
    const defaultDateRange = typeof customDateRange != 'undefined' ? customDateRange : defaultDateRanges.this_month;
    const lmsDateRange = userAccountDashboardPage ? defaultDateRanges.all_time : defaultDateRange;
    storedPeriod = !userAccountDashboardPage ? localStorage.getItem('AnalyticsSelectedPeriod') : null;
    selectedPeriod = storedPeriod
        ? JSON.parse(storedPeriod)
        : lmsDateRange;
}

const baseColors = ['accent', 'success', 'danger', 'warning'];
const shades = ['100', '70', '50', '30', '0'];
const colorVariables = getColorVariables(baseColors, shades);

function getCssVariableValue(variableName) {
    return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
}

function getColorVariables(baseColors, shades) {
    const colors = {};
    baseColors.forEach(baseColor => {
        shades.forEach(shade => {
            const variableName = `--${baseColor}-${shade}`;
            colors[`${baseColor}${shade}`] = getCssVariableValue(variableName);
        });
    });
    return colors;
}

function createChart(ctx, type, labels = [], datasets = [], currency = false) {
    const defaultDatasetSettings = {
        data: [],
        fill: 'start',
        borderWidth: 1,
        pointBackgroundColor: colorVariables.accent100,
        pointBorderColor: colorVariables.accent100,
        pointRadius: 3,
    };

    const colors = [
        [colorVariables.accent30, colorVariables.accent0],
        [colorVariables.success30, colorVariables.success0],
        [colorVariables.warning30, colorVariables.warning0],
        [colorVariables.danger30, colorVariables.danger0],
        ['rgba(123, 77, 255, 0.3)', 'rgba(123, 77, 255, 0)'],
    ];

    const defaultLineColors = {
        backgroundColor: ctx => getBackgroundColor(ctx, colorVariables.accent30, colorVariables.accent0),
        borderColor: colorVariables.accent100,
    };

    if (type === 'line' && datasets.length === 0) {
        datasets = [{ ...defaultLineColors }];
    }

    const preparedDatasets = datasets.map((dataset, index) => ({
        ...defaultDatasetSettings,
        ...(type === 'line' ? defaultLineColors : {}),
        backgroundColor: ctx => getBackgroundColor(ctx, colors[index % colors.length][0], colors[index % colors.length][1]),
        borderColor: colors[index % colors.length][0].replace('0.3', '1'),
        pointBorderColor: colors[index % colors.length][0].replace('0.3', '1'),
        pointBackgroundColor: colors[index % colors.length][0].replace('0.3', '1'),
        ...dataset
    }));

    return new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: type === 'doughnut' ? [{
                data: [],
                cutout: '80%',
                backgroundColor: [
                    colorVariables.accent100,
                    colorVariables.accent70,
                    colorVariables.accent50,
                    colorVariables.accent30,
                ],
            }] : preparedDatasets,
        },
        options: {
            ...(type === 'line' && {
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
            }),
            scales: type === 'line' ? {
                x: {
                    grid: {
                        display: true,
                        drawOnChartArea: false,
                        drawTicks: true,
                        color: 'rgba(219,224,233,1)',
                        borderColor: 'rgba(77,94,111,1)',
                    },
                    ticks: {
                        color: 'rgba(128,140,152,1)',
                        font: {
                            weight: '500'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(219,224,233,1)',
                        borderColor: 'rgba(77,94,111,1)',
                    },
                    ticks: {
                        color: 'rgba(128,140,152,1)',
                        font: {
                            weight: '500'
                        },
                        callback: function(value) {
                            return Number.isInteger(value) ? value : null;
                        }
                    },
                    border: {
                        display: false,
                    }
                }
            } : {},
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                },
                tooltip: {
                    position: 'nearest',
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            let label = '';

                            if (context.chart.config.type === 'doughnut') {
                                label = context.label || '';
                                if (label) {
                                    label = ' ' + label + ': ';
                                }
                                if (context.raw !== null) {
                                    if (currency) {
                                        label += formatCurrency(context.raw);
                                    } else {
                                        label += context.raw;
                                    }
                                }
                            } else {
                                label = context.dataset.label || '';
                                if (label) {
                                    label = ' ' + label + ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (currency) {
                                        label += formatCurrency(context.parsed.y);
                                    } else {
                                        label += context.parsed.y;
                                    }
                                }
                            }

                            return label;
                        },
                        labelPointStyle: function(context) {
                            return {
                                pointStyle: 'rect',
                                rotation: 0,
                                borderColor: 'transparent',
                                borderWidth: 0,
                            };
                        },
                        labelColor: function(context) {
                            return {
                                borderColor: 'transparent',
                                backgroundColor: context.chart.config.type === 'line' ? context.dataset.borderColor || context.dataset.pointBackgroundColor : context.dataset.backgroundColor[context.dataIndex],
                                borderWidth: 0
                            };
                        }
                    }
                }
            }
        }
    });
}

function updateLineChart(chart, labels, items) {
    chart.data.labels = labels;
    items.forEach((item, index) => {
        chart.data.datasets[index].label = item.label;
        chart.data.datasets[index].data = item.values;
    });
    chart.update();
}

function updateDoughnutChart(chart, info, type = '') {
    const valuesExists = info.values && info.values.length;
    const hasValues = info.values && info.values.length && info.values.reduce((a, b) => a + b, 0) !== 0;
    const hasPercents = info.percents && info.percents.length && info.percents.reduce((a, b) => a + b, 0) !== 0;
    chart.data.labels = info.labels;
    chart.data.datasets[0].data = valuesExists ? info.values : info.percents;
    chart.update();

    const infoBlocks = chart.canvas.parentNode.nextElementSibling.querySelectorAll('.masterstudy-doughnut-chart__info-block');
    info.labels.forEach((label, index) => {
        if (infoBlocks[index]) {
            infoBlocks[index].querySelector('.masterstudy-doughnut-chart__info-title').innerText = label;
            infoBlocks[index].querySelector('.masterstudy-doughnut-chart__info-percent').innerText = `${info.percents[index]}%`;
            if (valuesExists) {
                infoBlocks[index].querySelector('.masterstudy-doughnut-chart__info-value').innerText = type === 'currency' ? formatCurrency(info.values[index]) : info.values[index];
            }
        }
    });

    const emptyChartElement = chart.canvas.parentNode.querySelector('.masterstudy-analytics-empty-chart');

    if (hasValues || hasPercents) {
        emptyChartElement.style.display = 'none';
    } else {
        emptyChartElement.style.display = 'flex';
    }
}

function createGradient(ctx, chartArea, startColor, endColor) {
    if (!chartArea || !startColor || !endColor) {
        return startColor || endColor || 'rgba(0, 0, 0, 0)';
    }

    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    gradient.addColorStop(0, startColor);
    gradient.addColorStop(0.8, endColor);

    return gradient;
}

function getBackgroundColor(ctx, startColor, endColor) {
    const chart = ctx.chart;
    const { ctx: context, chartArea } = chart;

    if (!chartArea) {
        return null;
    }

    return createGradient(context, chartArea, startColor, endColor);
}

function updateStatsBlock(selector, value, type = 'default') {
    const statsBlock = document.querySelector(selector);
    if (statsBlock) {
        const valueElement = statsBlock.querySelector('.masterstudy-stats-block__value');
        if (valueElement) {
            if (type === 'currency') {
                valueElement.innerText = formatCurrency(value);
            } else {
                valueElement.innerText = value;
            }
        }
    }
}

function updateTotal(selector, total, type) {
    const totalElement = document.querySelector(selector);
    if (totalElement) {
        if (type === 'percent') {
            totalElement.innerText = `${total}%`;
        } else if (type === 'currency') {
            totalElement.innerText = formatCurrency(total);
        } else {
            totalElement.innerText = total;
        }
    }
}

function createDataTable(selector, columns, additionalOptions = {}) {
    const defaultOptions = {
        data: [],
        retrieve: true,
        processing: true,
        serverSide: true,
        columns: columns,
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: {
                paging: {
                    numbers: 5,
                }
            },
            bottomEnd: {
                pageLength: {
                    menu: [10, 25, 50],
                }
            },
        },
        language: {
            lengthMenu: '_MENU_' + stats_data.per_page_placeholder,
            emptyTable: stats_data.not_available,
            zeroRecords: stats_data.not_found,
        }
    };

    const options = Object.assign({}, defaultOptions, additionalOptions);

    return new DataTable(selector, options);
}

function updateDataTable(table, selector, loaders, currentRoute, pageData, dataSrcCallback, columnDefs = [], reloadTable = false, hidePagination = false, isLessonsTable = false, lessonsData = [], searchFieldValue = '') {
    if (!isDomReady) return;

    if (!table || reloadTable) {
        loaders.forEach(loader => {
            showLoaders(loader);
        });

        if (table) {
            table.clear().destroy();
            table = null;
            jQuery(selector).empty();
        }

        let additionalOptions = {
            ajax: {
                url: api.getRouteUrl(currentRoute),
                type: 'POST',
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', api.getRouteNonce());
                },
                data: function (d) {
                    d.date_from = getDateFrom();
                    d.date_to = getDateTo();
                    d.search.value = searchFieldValue;
                    if(selector === '#masterstudy-datatable-lessons') {
                        d.type = document.getElementById('masterstudy-analytics-course-page-types').value;
                    }
                },
                dataSrc: dataSrcCallback,
                complete: function() {
                    loaders.forEach(loader => {
                        hideLoaders(loader);
                    });
                }
            },
            columnDefs: columnDefs,
        };

        if (isLessonsTable) {
            additionalOptions.ajax.data = function (d) {
                d.date_from = getDateFrom();
                d.date_to = getDateTo();
                d.search.value = searchFieldValue;
                if(document.getElementById('masterstudy-analytics-course-page-orders')) {
                    d.sort = document.getElementById('masterstudy-analytics-course-page-orders').value;
                }
            }
            lessonsData.forEach(item => {
                pageData.push({
                    title: '<img src="' + stats_data.img_route + '/assets/icons/lessons/' + item.lesson_type + '.svg' + '" class="masterstudy-datatables-lesson-icon"></img>' + item.lesson_name,
                    data: item.lesson_id,
                    orderable: false,
                    tooltip: item.lesson_name,
                    render: function (data, type, row, meta) {
                        if (data === '-') {
                            return `<div class="masterstudy-datatables-lesson-type masterstudy-datatables-lesson-type_progress">
                                <div class="masterstudy-datatables-lesson-tooltip">` + stats_data.progress_lesson + `</div></div>`;
                        }else if (data === '0') {
                            return `<div class="masterstudy-datatables-lesson-type">
                                <div class="masterstudy-datatables-lesson-tooltip">` + stats_data.not_started_lesson + `</div></div>`;
                        } else if (data === '1') {
                            return `<div class="masterstudy-datatables-lesson-type masterstudy-datatables-lesson-type_complete">
                                <div class="masterstudy-datatables-lesson-tooltip">` + stats_data.completed_lesson + `</div></div>`;
                        } else if (data === '-1') {
                            return `<div class="masterstudy-datatables-lesson-type masterstudy-datatables-lesson-type_failed">
                                <div class="masterstudy-datatables-lesson-tooltip">` + stats_data.failed_lesson + `</div></div>`;
                        }
                    }
                });
            });

            pageData.push({
                title: '',
                data: 'last',
                orderable: false
            });

            additionalOptions = {
                ...additionalOptions,
                columnDefs: [
                    { targets: 0, width: '30px', orderable: false },
                ],
                headerCallback: function(nHead) {
                    if (!jQuery(nHead).find('.masterstudy-datatables-skew').length) {
                        jQuery(nHead).find('th.dt-orderable-none').not('[data-dt-column="0"]').wrapInner(
                            '<div class="masterstudy-datatables-skew"><div class="masterstudy-datatables-skew__wrapper"><div class="masterstudy-datatables-skew__container"></div></div><div class="masterstudy-datatables-skew__lines"></div></div>'
                        );
                    }
                },
                initComplete: function() {
                    loaders.forEach(loader => {
                        hideLoaders(loader);
                    });

                    this.api().columns().header().to$().each(function() {
                        jQuery(this).find('.masterstudy-datatables-skew')
                            .append('<div class="masterstudy-datatables-skew__tooltip"><span>' + jQuery(this).text() + '</span></div>')
                    });
                    jQuery('.masterstudy-datatables-skew__lines')
                        .mouseover(function(event) {
                            jQuery(this).parent().find('.masterstudy-datatables-skew__tooltip').addClass('masterstudy-datatables-skew__tooltip_active');
                        })
                        .mouseout(function() {
                            jQuery(this).parent().find('.masterstudy-datatables-skew__tooltip').removeClass('masterstudy-datatables-skew__tooltip_active');
                        });
                },
                columns: pageData,
            };
        }

        // Initialize the DataTable
        table = createDataTable(selector, pageData, additionalOptions);
        observeTableChanges(table, hidePagination);
    }

    return table;
}

function createDatepicker(selector, options = {}) {
    const localeObject = flatpickr.l10ns[stats_data.locale['current_locale']];

    const defaultOptions = {
        inline: true,
        mode: 'range',
        monthSelectorType: 'static',
        locale: {
            ...localeObject,
            firstDayOfWeek: stats_data.locale['firstDayOfWeek']
        }
    };

    const finalOptions = Object.assign({}, defaultOptions, options);

    return flatpickr(selector, finalOptions);
}

function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };

    return new Date(date).toLocaleDateString('en-US', options);
}

function formatDateForFetch(date) {
    if (!date) {
        return '';
    }

    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function getDateFrom() {
    return formatDateForFetch(selectedPeriod[0]);
}

function getDateTo() {
    return formatDateForFetch(selectedPeriod[1]);
}

function getPercentesByValues(values) {
    const total = values.reduce((acc, value) => acc + value, 0);

    const percentages = values.map(value => value > 0 ? (value / total * 100) : 0);

    const roundedPercentages = percentages.map(Math.round);

    const roundedTotal = roundedPercentages.reduce((acc, value) => acc + value, 0);

    // Adjust the last percentage to ensure the total is 100
    if (roundedTotal !== 100) {
        const difference = 100 - roundedTotal;
        const index = roundedPercentages.findIndex(value => value > 0);
        roundedPercentages[index] += difference;
    }

    return roundedPercentages;
}

function getDefaultDateRanges() {
    const now = new Date();
    const today = [new Date(), new Date()];
    const yesterday = [new Date(now.setDate(now.getDate() - 1)), new Date(now)];

    const startOfThisWeek = new Date(now.setDate(now.getDate() - now.getDay() + 1));
    const thisWeek = [new Date(startOfThisWeek), new Date()];

    const startOfLastWeek = new Date(now.setDate(now.getDate() - now.getDay() - 6));
    const endOfLastWeek = new Date(now.setDate(startOfLastWeek.getDate() + 6));
    const lastWeek = [startOfLastWeek, endOfLastWeek];

    const startOfThisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const thisMonth = [startOfThisMonth, new Date()];

    const startOfLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const endOfLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
    const lastMonth = [startOfLastMonth, endOfLastMonth];

    const startOfThisYear = new Date(now.getFullYear(), 0, 1);
    const thisYear = [startOfThisYear, new Date()];

    const startOfLastYear = new Date(now.getFullYear() - 1, 0, 1);
    const endOfLastYear = new Date(now.getFullYear() - 1, 11, 31);
    const lastYear = [startOfLastYear, endOfLastYear];

    const allTime = [new Date(0), new Date()];

    return {
        today: today,
        yesterday: yesterday,
        this_week: thisWeek,
        last_week: lastWeek,
        this_month: thisMonth,
        last_month: lastMonth,
        this_year: thisYear,
        last_year: lastYear,
        all_time: allTime,
    };
}

function closeDatepickerModal() {
    document.querySelector('.masterstudy-datepicker-modal').classList.remove('masterstudy-datepicker-modal_open');
    document.body.classList.remove('masterstudy-datepicker-body-hidden');
}

function closeSettingsModal() {
    document.querySelector('.masterstudy-settings-modal').classList.remove('masterstudy-settings-modal_open');
    document.body.classList.remove('masterstudy-settings-modal-body-hidden');
}

function chartsVisibilityControl() {
    document.querySelectorAll('[data-chart-id]').forEach(function(element) {
        element.style.display = 'flex';
    });
    if (selectedSettingsIds.length > 0) {
        selectedSettingsIds.forEach(id => {
            const targetContainer = document.querySelector(`[data-chart-id="${id}"]`);
            if (targetContainer) {
                targetContainer.style.display = 'none';
            }
        });
    }
}

function saveChartsVisibility() {
    localStorage.setItem('chartsVisibilityIds', JSON.stringify(selectedSettingsIds));
}

function loadChartsVisibility() {
    const savedIds = localStorage.getItem('chartsVisibilityIds');
    if (savedIds) {
        selectedSettingsIds = JSON.parse(savedIds);
        chartsVisibilityControl();
    }

    document.querySelectorAll('.masterstudy-settings-modal__item-wrapper').forEach(function(wrapper) {
        const parentId = wrapper.parentNode.id;

        if (selectedSettingsIds.includes(parentId)) {
            wrapper.classList.remove('masterstudy-settings-modal__item-wrapper_fill');
        } else {
            wrapper.classList.add('masterstudy-settings-modal__item-wrapper_fill');
        }
    });
}

function resetTime(date) {
    const d = typeof date === 'string' ? new Date(date) : date;

    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function updateDates(period, datepicker = null, firstTime = false, saveToLocale = true) {
    if (!period) {
        return;
    }

    const periodStart = resetTime(period[0]);
    const periodEnd = resetTime(period[1]);
    const selectedStart = resetTime(selectedPeriod[0]);
    const selectedEnd = resetTime(selectedPeriod[1]);

    if (!firstTime && periodStart.getTime() === selectedStart.getTime() && periodEnd.getTime() === selectedEnd.getTime()) {
        return;
    }

    selectedPeriod = period;
    let isDefaultPeriod = false;
    let defaultPeriodKey = null;

    document.querySelectorAll('.masterstudy-datepicker-modal__single-item').forEach(function(item) {
        const periodKey = item.id.replace('masterstudy-datepicker-modal-', '');

        if (defaultDateRanges[periodKey][0].toDateString() === selectedPeriod[0].toDateString() &&
            defaultDateRanges[periodKey][1].toDateString() === selectedPeriod[1].toDateString()) {
            isDefaultPeriod = true;
            defaultPeriodKey = periodKey;
            item.classList.add('masterstudy-datepicker-modal__single-item_fill');
            document.querySelector('.masterstudy-analytics__date-label').textContent = item.textContent.trim();
        } else {
            item.classList.remove('masterstudy-datepicker-modal__single-item_fill');
        }
    });


    if (!firstTime) {
        const event = new CustomEvent('datesUpdated', { detail: { selectedPeriod } });
        document.dispatchEvent(event);
    }

    if (datepicker) {
        datepicker.setDate(selectedPeriod, true);
    }

    if ( document.querySelector('.masterstudy-analytics__date-value') ) {
        document.querySelector('.masterstudy-analytics__date-value').textContent = `${formatDate(selectedPeriod[0])} - ${formatDate(selectedPeriod[1])}`;
    }

    if (isDefaultPeriod && saveToLocale) {
        localStorage.setItem('AnalyticsSelectedPeriodKey', defaultPeriodKey);
    } else {
        document.querySelectorAll('.masterstudy-datepicker-modal__single-item').forEach(function(item) {
            item.classList.remove('masterstudy-datepicker-modal__single-item_fill');
        });
        if ( document.querySelector('.masterstudy-analytics__date-label') ) {
            document.querySelector('.masterstudy-analytics__date-label').textContent = stats_data.custom_period;
        }
        if ( saveToLocale ) {
            localStorage.setItem('AnalyticsSelectedPeriod', JSON.stringify(selectedPeriod));
            localStorage.removeItem('AnalyticsSelectedPeriodKey');
        }
    }
}

function initializeDatepicker(selector) {
    const datepickerElement = document.querySelector(selector);
    if (!datepickerElement) {
        console.error(`Element not found for selector: ${selector}`);

        return;
    }

    const datepicker = createDatepicker(selector, {
        dateFormat: 'M d, Y',
        defaultDate: selectedPeriod,
        maxDate: new Date(),
        onClose: function(selectedDates, dateStr, instance) {
            updateDates(selectedDates, datepicker);
            closeDatepickerModal();
        }
    });

    if (!(selectedPeriod[0] instanceof Date)) {
        selectedPeriod = selectedPeriod.map(dateStr => new Date(dateStr));
    }

    updateDates(selectedPeriod, datepicker, true);

    document.querySelector('.masterstudy-datepicker-modal__reset').addEventListener('click', function() {
        datepicker.setDate(defaultDateRanges.this_week, true);
        updateDates(defaultDateRanges.this_week, datepicker);
        document.querySelector('#masterstudy-datepicker-modal-this_week').classList.add('masterstudy-datepicker-modal__single-item_fill');
        Array.from(document.querySelector('#masterstudy-datepicker-modal-this_week').parentNode.children).forEach(function(sibling) {
            if (sibling !== document.querySelector('#masterstudy-datepicker-modal-this_week')) {
                sibling.classList.remove('masterstudy-datepicker-modal__single-item_fill');
            }
        });
    });

    document.querySelector('.masterstudy-datepicker-modal__close').addEventListener('click', function() {
        closeDatepickerModal();
    });

    document.querySelectorAll('.masterstudy-datepicker-modal__single-item').forEach(function(item) {
        item.addEventListener('click', function() {
            const period = this.id.replace('masterstudy-datepicker-modal-', '');
            if (defaultDateRanges[period]) {
                datepicker.setDate(defaultDateRanges[period], true);
                updateDates(defaultDateRanges[period], datepicker);
                document.querySelector('.masterstudy-analytics__date-label').textContent = this.textContent.trim();
                closeDatepickerModal();
            }
        });
    });

    document.querySelector('.masterstudy-analytics__date').addEventListener('click', function() {
        document.querySelector('.masterstudy-datepicker-modal').classList.add('masterstudy-datepicker-modal_open');
        document.body.classList.add('masterstudy-datepicker-body-hidden');
    });

    document.querySelector('.masterstudy-datepicker-modal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeDatepickerModal();
        }
    });
}

function getQueryParam(param) {
    let urlParams = new URLSearchParams(window.location.search);

    return urlParams.get(param);
}

function renderReportButton(url, detailedTitle = false) {
    const title = detailedTitle ? stats_data.details_title : stats_data.report_button_title;

    return '<div class="masterstudy-analytics-report-button__wrapper">' +
                '<a href="' + url + '" class="masterstudy-analytics-report-button">' + title + '</a>' +
           '</div>';
}

function renderCourseButtons(reportUrl, builderUrl, courseUrl) {
    return '<div class="masterstudy-analytics-report-button__wrapper">' +
                '<a href="' + reportUrl + '" class="masterstudy-analytics-report-button">' + stats_data.report_button_title + '</a>' +
                '<a href="' + builderUrl + '" class="masterstudy-analytics-builder-button"></a>' +
                '<a href="' + courseUrl + '" class="masterstudy-analytics-course-button"></a>' +
           '</div>';
}

function renderRating(rating) {
    const stars = [1, 2, 3, 4, 5];
    const filledStarClass = 'masterstudy-analytics-rating__star_filled';

    return '<div class="masterstudy-analytics-rating">' +
                stars.map(star => {
                    const starClass = star <= Math.floor(rating) ? filledStarClass : '';
                    return `<span class="masterstudy-analytics-rating__star ${starClass}"></span>`;
                }).join('') +
           '</div>';
}

function renderProgress(progress) {
    return '<div class="masterstudy-analytics-progress">' +
                '<div class="masterstudy-analytics-progress__bars">' +
                    '<span class="masterstudy-analytics-progress__bar-empty"></span>' +
                    '<span class="masterstudy-analytics-progress__bar-filled" style="width:' + progress + '%"></span>' +
                '</div>' +
                '<div class="masterstudy-analytics-progress__bottom">' +
                    '<div class="masterstudy-analytics-progress__title">' +
                        stats_data.progress_title + ':' +
                        '<span class="masterstudy-analytics-progress__percent">' + progress + '%</span>' +
                '</div>' +
           '</div>';
}

function hideLoaders(selector) {
    const elements = document.querySelectorAll(selector);

    elements.forEach(element => {
        const loaders = element.querySelectorAll('.masterstudy-analytics-loader');
        loaders.forEach(loader => {
            loader.style.display = 'none';
        });
    });
}

function showLoaders(selector) {
    const elements = document.querySelectorAll(selector);

    elements.forEach(element => {
        const loaders = element.querySelectorAll('.masterstudy-analytics-loader');
        loaders.forEach(loader => {
            loader.style.display = 'flex';
        });
    });
}

function formatCurrency(value) {
    let formattedValue = Number(value).toFixed(stats_data.decimals_num);
    let parts = formattedValue.split('.');

    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, stats_data.currency_thousands);

    formattedValue = parts.join(stats_data.currency_decimals);

    if (stats_data.currency_position === 'left') {
        return stats_data.currency_symbol + formattedValue;
    } else {
        return formattedValue + stats_data.currency_symbol;
    }
}

function tablePaginationVisibility(table, hide = false) {
    const tableWrapper = table.table().container();
    const paginationStart = tableWrapper.querySelector('.dt-layout-cell.dt-start');
    const paginationEnd = tableWrapper.querySelector('.dt-layout-cell.dt-end');

    if (table.data().count() === 0 || hide) {
        if (paginationStart) paginationStart.style.display = 'none';
        if (paginationEnd) paginationEnd.style.display = 'none';
    } else {
        if (paginationStart) paginationStart.style.display = '';
        if (paginationEnd) paginationEnd.style.display = '';
    }
}

function observeTableChanges(table, hide = false) {
    const tableWrapper = table.table().container();
    const observer = new MutationObserver(function() {
        tablePaginationVisibility(table, hide);
    });

    observer.observe(tableWrapper, {
        childList: true,
        subtree: true,
    });

    const intersectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                table.columns.adjust();
            }
        });
    }, { threshold: 0.1 });

    intersectionObserver.observe(tableWrapper);
}

function searchFieldIntent(target) {
    // Dispatch a custom event with the search value
    const searchEvent = new CustomEvent('intentTableSearch', {
        detail: {
            searchValue: target.value.trim(),
            searchTarget: target,
        }
    });
    document.dispatchEvent(searchEvent);
}

document.addEventListener('DOMContentLoaded', function() {
    loadChartsVisibility();

    isDomReady = true;
    const settingsButton = document.querySelector('.masterstudy-settings-button');
    const settingsModal  = document.querySelector('.masterstudy-settings-modal');

    if (settingsButton) {
        document.querySelector('.masterstudy-settings-button').addEventListener('click', function() {
            settingsModal.classList.add('masterstudy-settings-modal_open');
            document.body.classList.add('masterstudy-settings-modal-body-hidden');
        });

        document.addEventListener('click', function(event) {
            const clickedDropdown = event.target.closest('.masterstudy-settings-dropdown');

            document.querySelectorAll('.masterstudy-settings-dropdown__menu_open').forEach(function(openDropdown) {
                if (openDropdown !== clickedDropdown?.querySelector('.masterstudy-settings-dropdown__menu')) {
                    openDropdown.classList.remove('masterstudy-settings-dropdown__menu_open');
                }
            });

            if (clickedDropdown) {
                const dropdownMenu = clickedDropdown.querySelector('.masterstudy-settings-dropdown__menu');
                dropdownMenu.classList.toggle('masterstudy-settings-dropdown__menu_open');
            }
        });

        document.querySelectorAll('.masterstudy-settings-dropdown__item').forEach(function(item) {
            item.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                selectedSettingsIds.push(id);
                chartsVisibilityControl();
                saveChartsVisibility();
                document.querySelector(`#${id}`).querySelector('.masterstudy-settings-modal__item-wrapper').classList.remove('masterstudy-settings-modal__item-wrapper_fill');
            });
        });
    }

    if(document.querySelectorAll('input[id*="-search"]').length) {
        document.querySelectorAll('input[id*="-search"]').forEach(function(selector) {
            selector.addEventListener('keydown', function (e) {
                const value = this;
                if (window.search_field_intent_timeout)
                    clearTimeout(window.search_field_intent_timeout);
                if (e.keyCode === 13) {
                    searchFieldIntent(value);
                } else {
                    window.search_field_intent_timeout = setTimeout(function () {
                        searchFieldIntent(value)
                    }, 1000);
                }
            });
        })
    }
    if(document.querySelector('div[class*="__search_wrapper"] .stmlms-search'))
        document.querySelector('div[class*="__search_wrapper"] .stmlms-search').addEventListener('click', function(event) {
            if(this.parentNode.querySelector('input').value !== '')
                searchFieldIntent(this.parentNode.querySelector('input'))
        });

    if (settingsModal) {
        setTimeout(function() {
            settingsModal.removeAttribute('style');
        }, 1000);

        settingsModal.addEventListener('click', function(event) {
            if (event.target === this) {
                closeSettingsModal();
            }
        });

        document.querySelector('.masterstudy-settings-modal__header-close').addEventListener('click', function() {
            closeSettingsModal();
        });

        document.querySelectorAll('.masterstudy-settings-modal__item-wrapper').forEach(function(wrapper) {
            wrapper.addEventListener('click', function() {
                this.classList.toggle('masterstudy-settings-modal__item-wrapper_fill');
                const parentId = this.parentNode.id;

                if (selectedSettingsIds.includes(parentId)) {
                    selectedSettingsIds = selectedSettingsIds.filter(id => id !== parentId);
                } else {
                    selectedSettingsIds.push(parentId);
                }

                chartsVisibilityControl();
                saveChartsVisibility();
            });
        });
    }
});
