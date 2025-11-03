// resources/js/discipline_dashboard.js
// Handles the dynamic pie chart for Minor vs Major Violations on the Discipline Dashboard

console.log('discipline_dashboard.js loaded');
document.addEventListener('DOMContentLoaded', function () {
  // CASE STATUS PIE CHART
  var caseStatusChartCanvas = document.getElementById('caseStatusPieChart');
  if (caseStatusChartCanvas) {
    var ctx = caseStatusChartCanvas.getContext('2d');
    var caseStatusPieChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Completed', 'In Progress', 'Pre-Completed'],
        datasets: [{
          data: [0, 0, 0],
          backgroundColor: ['#28a745', '#17a2b8', '#ffc107'], // green, blue, yellow
          borderColor: ['#28a745', '#17a2b8', '#ffc107'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'bottom'
          }
        }
      }
    });

    // Fetch data dynamically
    fetch(window.caseStatusStatsUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Case status API data:', data);
        if (!Object.prototype.hasOwnProperty.call(data, 'completed') ||
            !Object.prototype.hasOwnProperty.call(data, 'in_progress') ||
            !Object.prototype.hasOwnProperty.call(data, 'pre_completed')) {
          throw new Error('Invalid data (missing key): ' + JSON.stringify(data));
        }
        caseStatusPieChart.data.datasets[0].data = [
          Number(data.completed) || 0,
          Number(data.in_progress) || 0,
          Number(data.pre_completed) || 0
        ];
        caseStatusPieChart.update();
      })
      .catch(error => {
        console.error('Case status pie chart data fetch error:', error);
        var chartContainer = caseStatusChartCanvas.parentElement;
        // Remove any previous error message
        var prevError = chartContainer.querySelector('.text-danger.text-center.mt-3');
        if (prevError) prevError.remove();
        var errorDiv = document.createElement('div');
        errorDiv.className = 'text-danger text-center mt-3';
      //  errorDiv.innerText = 'Failed to load case status chart data.';
        chartContainer.appendChild(errorDiv);
      });
  }

  // MINOR VS MAJOR VIOLATIONS PIE CHART
  var violationPieChartCanvas = document.getElementById('violationPieChart');
  if (violationPieChartCanvas) {
    var vctx = violationPieChartCanvas.getContext('2d');
    var violationPieChart = new Chart(vctx, {
      type: 'pie',
      data: {
        labels: ['Minor Violations', 'Major Violations'],
        datasets: [{
          data: [0, 0],
          backgroundColor: ['#43b864', '#217a36'],
          borderColor: ['#43b864', '#217a36'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'bottom'
          }
        }
      }
    });

    fetch(window.violationStatsUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (!Object.prototype.hasOwnProperty.call(data, 'minor') || !Object.prototype.hasOwnProperty.call(data, 'major')) {
          throw new Error('Invalid data (missing key): ' + JSON.stringify(data));
        }
        violationPieChart.data.datasets[0].data = [
          Number(data.minor) || 0,
          Number(data.major) || 0
        ];
        violationPieChart.update();
      })
      .catch(error => {
        console.error('Minor vs Major pie chart data fetch error:', error);
        var chartContainer = violationPieChartCanvas.parentElement;
        var prevError = chartContainer.querySelector('.text-danger.text-center.mt-3');
        if (prevError) prevError.remove();
        var errorDiv = document.createElement('div');
        errorDiv.className = 'text-danger text-center mt-3';
        errorDiv.innerText = 'Failed to load minor/major violation chart data.';
        chartContainer.appendChild(errorDiv);
      });
  }

  // BAR CHART
  var barCanvas = document.getElementById('violationBarChart');
  if (barCanvas) {
    var barCtx = barCanvas.getContext('2d');
    var barChart = new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [
          {
            label: 'Minor Violations',
            backgroundColor: '#43b864',
            borderColor: '#43b864',
            borderWidth: 2,
            data: [],
          },
          {
            label: 'Major Violations',
            backgroundColor: '#217a36',
            borderColor: '#217a36',
            borderWidth: 2,
            data: [],
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'bottom'
          }
        },
        scales: {
          x: {
            stacked: false
          },
          y: {
            beginAtZero: true,
            stacked: false
          }
        }
      }
    });

    fetch(window.violationBarUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (!Array.isArray(data.labels) || !Array.isArray(data.minor) || !Array.isArray(data.major)) {
          throw new Error('Invalid bar chart data: ' + JSON.stringify(data));
        }
        barChart.data.labels = data.labels;
        barChart.data.datasets[0].data = data.minor;
        barChart.data.datasets[1].data = data.major;
        barChart.update();
      })
      .catch(error => {
        console.error('Bar chart data fetch error:', error);
        var chartContainer = barCanvas.parentElement;
        var errorDiv = document.createElement('div');
        errorDiv.className = 'text-danger text-center mt-3';
        errorDiv.innerText = 'Failed to load bar chart data.';
        chartContainer.appendChild(errorDiv);
      });
  }
});
