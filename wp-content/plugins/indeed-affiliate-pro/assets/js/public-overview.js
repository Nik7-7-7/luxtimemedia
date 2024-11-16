/*
 *Ultimate Affiliate Pro - Front-end Overview Data
 */
"use strict";
window.addEventListener( 'DOMContentLoaded', function(){
  var uapDates = [];
  var uapAmounts = [];
  var uapLabels = [];
  var uapAmountsBaseValues = [];
  jQuery( '.uap-js-overview-stats-last-30' ).each(function( e, html ){
      uapDates.push( jQuery(html).attr( 'data-date' ) );
      uapAmounts.push( jQuery(html).attr( 'data-amount' ) );
      uapLabels.push( jQuery(html).attr( 'data-label' ) );
      uapAmountsBaseValues.push( jQuery(html).attr( 'data-base_amount' ) );
  });

var options = {
  maintainAspectRatio: false,
  spanGaps: false,
  elements: {
    line: {
      tension: 0.01
    }
  },
  plugins: {
    filler: {
      propagate: false
    }
  },
  scales: {
    x: {
      ticks: {
        autoSkip: false,
        maxRotation: 0,
      },
    },
    xAxes: [ { gridLines: { display: false } } ],
    yAxes: [{
           ticks: {
               beginAtZero: true,
               userCallback: function(label, index, labels) {
                   // when the floored value is the same as the value we have a whole number
                   if (Math.floor(label) === label) {
                       return label;
                   }

               },
           },
          scaleLabel: {
            display: true,
            labelString: jQuery( '.uap-js-overview-earnings-received-label' ).attr('data-value')
          }
    }],
    scaleShowVerticalLines: false,
  },
  animation: {
        duration		: 2000,
        easing			: 'easeInQuad',
      },
}; // end of options

  var uapChart = new Chart('chart-1', {
    type    : 'line',
    data    : {
      labels    : uapLabels,
      datasets  : [{
          backgroundColor   : 'rgba(201,218,232,0.5)',
          borderColor       : '#c9dae8',
          data              : uapAmountsBaseValues, //referrals counts
          label             : jQuery( '.uap-js-overview-earnings-label').attr( 'data-value' ),
          pointRadius				: 3,
          fill							: 'start',
          borderWidth				: 2
      }]
    },
    options : Chart.helpers.merge(options, {
              legend: {
                    display: false
              },
              title: {
                text    : '',
                display : true
              },
              tooltips: {
                    intersect		: false,
                    position		: 'nearest',
                    callbacks: {
                      label: function(tooltipItem, data) {
                          var string = '';
                          uapAmounts.forEach(function( val, i ){
                              if ( i == tooltipItem.index ){
                                string = val;
                              }
                          });
                          return string;
                      },
                      title: function(tooltipItem, data) {
                        var string = '';
                        uapDates.forEach(function( val, i ){
                            if ( i == tooltipItem[0].index ){
                              string = val;
                            }
                        });
                        return string;
                      },
                    }
                }
    }),

  });

});
