(function () {
    var chart = document.getElementById('payout_stats_chart');
    var data = window.ohnousPayoutStats || {};
    if (!chart || !window.ApexCharts) return;
    new ApexCharts(chart, {
        chart: {type: 'donut', height: 280, fontFamily: 'inherit', toolbar: {show: false}, animations: {enabled: true}},
        colors: ['#2f9e67', '#d59a2d', '#d64545'],
        series: [Number(data.successful || 0), Number(data.pending || 0), Number(data.failed || 0)],
        labels: ['Réussis', 'En cours', 'Échoués'],
        legend: {position: 'bottom'},
        dataLabels: {enabled: false},
        responsive: [{breakpoint: 575, options: {chart: {height: 240}, legend: {position: 'bottom'}}}]
    }).render();
})();
