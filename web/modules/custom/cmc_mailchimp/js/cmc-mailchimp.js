// Hacky code to get patternlab to play nice w/ drupal JS
/*if (typeof drupalSettings === 'undefined') {
  drupalSettings = {};
}
*/

(function ($, Drupal, drupalSettings) {
  // Highcharts
  Drupal.behaviors.cmc_mailchimp__highcharts = {
    attach: function (context, settings) {

      $(document).ready(function() {
        // Get mc data
        var emails_open = drupalSettings.emails_open;
        var emails_sent = drupalSettings.emails_sent;
        var emails_not_opened = 100 - emails_open;

        // Emails opened
        Highcharts.chart('mailchimp-contact-report__email-opened', {
          chart: {
            backgroundColor:'transparent',
    				plotBorderWidth: 0,
    				plotShadow: false,
    				type: 'pie',
    				renderTo: 'chart',
    				spacing: [0,0,0,0],
            height:250
          },
          title: {
            text: '<span class="count">' + emails_open +'%</span><br><span class="text">Emails Opened</span>',
            align: 'center',
            verticalAlign: 'middle',
            y: 0
          },
          tooltip: {
            enabled:false,
          },
          plotOptions: {
            pie: {
              dataLabels: {
                enabled: false,
              }
            }
          },
          series: [{
            type: 'pie',
            name: 'Browser share',
            innerSize: '70%',
            data: [parseInt(emails_open), parseInt(emails_not_opened)],
          }],
          exporting: {
            enabled: false
          },
          credits: {
            enabled: false
          },
        });

        // Emails recieved
        Highcharts.chart('mailchimp-contact-report__email-received', {
          chart: {
            backgroundColor:'transparent',
    				plotBorderWidth: 0,
    				plotShadow: false,
    				type: 'pie',
    				renderTo: 'chart',
    				spacing: [0,0,0,0],
            height:250
          },
          title: {
            text: '<span class="count">' + emails_sent + '</span><br><span class="text">Emails Received</span>',
            align: 'center',
            verticalAlign: 'middle',
            y: 0
          },
          tooltip: {
            enabled:false,
          },
          plotOptions: {
            pie: {
              dataLabels: {
                enabled: false,
              }
            }
          },
          series: [{
            type: 'pie',
            name: 'Browser share',
            innerSize: '70%',
            data: [100, 0],
          }],
          exporting: {
            enabled: false
          },
          credits: {
            enabled: false
          },
        });
      });

    }
  };

})(jQuery, Drupal, drupalSettings);
