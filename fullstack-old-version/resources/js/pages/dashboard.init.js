/*
Template Name: Skote - Admin & Dashboard Template
Author: Themesbrand
Website: https://themesbrand.com/
Contact: themesbrand@gmail.com
File: Dashboard Init Js File
*/

// get colors array from the string
function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");
        if (colors) {
            colors = JSON.parse(colors);
            return colors.map(function (value) {
                var newValue = value.replace(" ", "");
                if (newValue.indexOf(",") === -1) {
                    var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                    if (color) return color;
                    else return newValue;;
                } else {
                    var val = value.split(',');
                    if (val.length == 2) {
                        var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                        rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                        return rgbaColor;
                    } else {
                        return newValue;
                    }
                }
            });
        }
    }
}

//  subscribe modal

setTimeout(function () {
    $('#subscribeModal').modal('show');
}, 2000);


// stacked column chart
var linechartBasicColors = getChartColorsArray("stacked-column-chart");
if (linechartBasicColors) {
    var chartElement = document.querySelector("#stacked-column-chart");
    var sentEmailCounts = new Array(12).fill(0);
    if (chartElement) {
        var sentEmails = chartElement.getAttribute("data-sent-emails");
        if (sentEmails) {
            try {
                var parsedSentEmails = JSON.parse(sentEmails);
                if (Array.isArray(parsedSentEmails)) {
                    parsedSentEmails.slice(0, 12).forEach(function (value, index) {
                        sentEmailCounts[index] = value;
                    });
                }
            } catch (error) {
                sentEmailCounts = sentEmailCounts;
            }
        }
    }

    var options = {
        chart: {
            height: 360,
            type: 'bar',
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: true
            }
        },

        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '15%',
                endingShape: 'rounded',
                borderRadius: 5,
            },
        },

        dataLabels: {
            enabled: false
        },
        series: [{
            name: 'Đã Gửi',
            data: sentEmailCounts
        }],
        xaxis: {
            categories: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
        },
        colors: linechartBasicColors,
        legend: {
            position: 'bottom',
        },
        fill: {
            opacity: 1
        },
    }

    var chart = new ApexCharts(
        document.querySelector("#stacked-column-chart"),
        options
    );

    chart.render();
}

// Radial chart
var radialbarColors = getChartColorsArray("radialBar-chart");
if (radialbarColors) {
    var radialEl = document.querySelector("#radialBar-chart");
    var radialSeries = 0;
    var radialLabel = 'Độ hoàn thành';
    if (radialEl) {
        var attrSeries = radialEl.getAttribute("data-series");
        var attrLabel = radialEl.getAttribute("data-label");
        radialSeries = attrSeries ? parseFloat(attrSeries) : 0;
        if (isNaN(radialSeries)) radialSeries = 0;
        if (attrLabel) radialLabel = attrLabel;
    }

    var options = {
        chart: {
            height: 200,
            type: 'radialBar',
            offsetY: -10
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 135,
                dataLabels: {
                    name: {
                        fontSize: '13px',
                        color: undefined,
                        offsetY: 60
                    },
                    value: {
                        offsetY: 22,
                        fontSize: '16px',
                        color: undefined,
                        formatter: function (val) {
                            return val + "%";
                        }
                    }
                }
            }
        },
        colors: radialbarColors,
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                shadeIntensity: 0.15,
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 50, 65, 91]
            },
        },
        stroke: {
            dashArray: 4,
        },
        series: [radialSeries],
        labels: [radialLabel],

    }

    var chart = new ApexCharts(
        document.querySelector("#radialBar-chart"),
        options
    );

    chart.render();
}
