/**
 * RC Manager – renders statistics charts with Chart.js from data-* attributes.
 */
(function () {
    'use strict';

    var PALETTE = ['#2271b1', '#d63638', '#00a32a', '#dba617', '#8c5fc7', '#3582c4', '#e26f56', '#1d9a6c', '#b26200', '#646970', '#c3196e', '#0a4b78'];

    function parse(value) {
        try {
            return JSON.parse(value);
        } catch (e) {
            return [];
        }
    }

    function render(canvas) {
        var type = canvas.getAttribute('data-type');
        var labels = parse(canvas.getAttribute('data-labels'));
        var values = parse(canvas.getAttribute('data-values'));
        var isPie = type === 'pie';

        var colors = labels.map(function (_, i) {
            return PALETTE[i % PALETTE.length];
        });

        new window.Chart(canvas, {
            type: isPie ? 'doughnut' : type,
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: type === 'line' ? 'rgba(34, 113, 177, 0.15)' : colors,
                    borderColor: type === 'line' ? PALETTE[0] : (isPie ? '#fff' : colors),
                    borderWidth: type === 'line' ? 2 : 1,
                    fill: type === 'line',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: isPie, position: 'bottom' }
                },
                scales: isPie ? {} : {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Chart) {
            return;
        }
        Array.prototype.forEach.call(document.querySelectorAll('.rcman-chart canvas'), render);
    });
})();
