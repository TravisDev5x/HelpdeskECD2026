$(document).ready(function() {
  moment.defineLocale('es', null);

  fechaInicio = new moment().add(0, 'months').date(1);
  fechaInicio = fechaInicio.format('YYYY-MM-DD');
  $("#fecha-inicio").val(fechaInicio);

  fechaFin = moment().format('YYYY-MM-DD');
  $("#fecha-fin").val(fechaFin);



  // Radialize the colors
  Highcharts.setOptions({
    colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
      return {
        radialGradient: {
          cx: 0.5,
          cy: 0.3,
          r: 0.7
        },
        stops: [
          [0, color],
          [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
        ]
      };
    })
  });

  Highcharts.setOptions({
    lang: {
      thousandsSep: ' ',
      decimalPoint: ','
    }
  });

  getDataReportArea();
  getDataReportFailure();
  getDataReportUser();
  getDataReportDay();
  getDataReportAreaSolution();
  getDataReportUserSolution();
  getDataReportTime();
});

$('#datepicker-inicio').on('change', function() {
  getDataReportArea();
  getDataReportFailure();
  getDataReportUser();
  getDataReportDay();
  getDataReportAreaSolution();
  getDataReportUserSolution();
  getDataReportTime();
});

$('#datepicker-fin').on('change', function() {
  getDataReportArea();
  getDataReportFailure();
  getDataReportUser();
  getDataReportDay();
  getDataReportAreaSolution();
  getDataReportUserSolution();
  getDataReportTime();
});

function getDataReportArea() {
  $.ajax({
    url: 'report-areas',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val()}
  })
  .done(function(data)
  {
    $("#tsv-area").empty();
    var tsv = "Respuesta Total\n";
    var total = 0;
    $.each(data, function (index, value){
      tsv += value.name + " \t" + value.total + "%\n";
      if(value.total) {
        total = parseFloat(total) + parseFloat(value.total);
      }
    });
    $("#tsv-area").append(tsv);
    showReportArea(total);
  });
}

function showReportArea(total) {
  Highcharts.data({
    csv: document.getElementById('tsv-area').innerHTML,
    itemDelimiter: '\t',
    parsed: function (columns) {
      var brands = {}, brandsData = [], versions = {}, drilldownSeries = [];

      // Parse percentage strings
      columns[1] = $.map(columns[1], function (value) {
        if (value.indexOf('%') === value.length - 1) {
          value = parseFloat(value);
        }
        return value;
      });
      $.each(columns[0], function (i, name) {
        var brand, version;

        if (i > 0) {
          // Remove special edition notes
          name = name.split(' -')[0];

          // Split into brand and version
          version = name.match(/([0-9]+[\.0-9x]*)/);

          if (version) {
            version = version[0];
          }
          brand = name.replace(version, '');

          // Create the main data
          if (!brands[brand]) {
            brands[brand] = columns[1][i];
          } else {
            brands[brand] += columns[1][i];
          }

          // Create the version data
          if (version !== null) {
            if (!versions[brand]) {
              versions[brand] = [];
            }
            versions[brand].push(['v' + version, columns[1][i]]);
          }
        }
      });
      $.each(brands, function (name, y) {
        brandsData.push({
          name: name,
          y: y,
          drilldown: versions[name] ? name : null
        });
      });
      var chart = {
        type: 'column'
      };
      var title = {
        text: 'Áreas que más tickets levantan'
      };
      var xAxis = {
        type: 'category'
      };
      var yAxis = {
        title: {
          text: 'Total de tickets'
        }
      };
      var plotOptions = {
        column: {
          allowPointSelect: false,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '{point.y}'
          },
          showInLegend: true
        },
        series: {
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '12px',
              fontWeight: 'normal',
              fill: '#f8f9fa',
              cursor: 'pointer'
            },
            softConnector: true
          },
          point: {
            events: {
              click: function () {
                var filter =  '';
                try {
                  filter = this.options.name;
                } catch (e) {
                }
                if(filter != '') {
                  showDatail(filter, 'Areas');
                }
              }
            }
          }
        }
      };
      var tooltip = {
        headerFormat: '<span style = "font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style = "color:{point.color}">{point.name}</span>: <b>{point.y}</b> de un total de ' + total
      };
      var credits = {
        enabled: false
      };
      var series = [{
        name: 'Áreas',
        colorByPoint: true,
        data: brandsData
      }];
      var json = {};
      json.chart = chart;
      json.title = title;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.plotOptions = plotOptions;
      json.tooltip = tooltip;
      json.credits = credits;
      json.series = series;
      $('#areas').highcharts(json);
    }
  });
}

function getDataReportFailure() {
  $.ajax({
    url: 'report-failures',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val()}
  })
  .done(function(data)
  {
    $("#tsv-failure").empty();
    var tsv = "Respuesta Total\n";
    var total = 0;
    $.each(data, function (index, value){
      tsv += value.name + " \t" + value.total + "%\n";
      if(value.total) {
        total = parseFloat(total) + parseFloat(value.total);
      }
    });
    $("#tsv-failure").append(tsv);
    showReportFailure(total);
  });
}

function showReportFailure(total) {
  Highcharts.data({
    csv: document.getElementById('tsv-failure').innerHTML,
    itemDelimiter: '\t',
    parsed: function (columns) {
      var brands = {}, brandsData = [], versions = {}, drilldownSeries = [];

      // Parse percentage strings
      columns[1] = $.map(columns[1], function (value) {
        if (value.indexOf('%') === value.length - 1) {
          value = parseFloat(value);
        }
        return value;
      });
      $.each(columns[0], function (i, name) {
        var brand, version;

        if (i > 0) {
          // Remove special edition notes
          name = name.split(' -')[0];

          // Split into brand and version
          version = name.match(/([0-9]+[\.0-9x]*)/);

          if (version) {
            // version = version[0];
          }
          brand = name.replace(version, '');

          // Create the main data
          if (!brands[brand]) {
            brands[brand] = columns[1][i];
          } else {
            brands[brand] += columns[1][i];
          }

          // Create the version data
          if (version !== null) {
            if (!versions[brand]) {
              versions[brand] = [];
            }
            versions[brand].push(['v' + version, columns[1][i]]);
          }
        }
      });
      $.each(brands, function (name, y) {
        brandsData.push({
          name: name,
          y: y,
          drilldown: versions[name] ? name : null
        });
      });
      var chart = {
        type: 'column'
      };
      var title = {
        text: 'Tipo de tickets que más levantan'
      };
      var xAxis = {
        type: 'category'
      };
      var yAxis = {
        title: {
          text: 'Total de tickets'
        }
      };
      var plotOptions = {
        column: {
          allowPointSelect: false,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '{point.y}'
          },
          showInLegend: true
        },
        series: {
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '12px',
              fontWeight: 'normal',
              fill: '#f8f9fa',
              cursor: 'pointer'
            },
            softConnector: true
          },
          point: {
            events: {
              click: function () {
                var filter =  '';
                try {
                  filter = this.options.name;
                } catch (e) {
                }
                if(filter != '') {
                  showDatail(filter, 'Failures');
                }
              }
            }
          }
        }
      };
      var tooltip = {
        headerFormat: '<span style = "font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style = "color:{point.color}">{point.name}</span>: <b>{point.y}</b> de un total de ' + total
      };
      var credits = {
        enabled: false
      };
      var series = [{
        name: 'Tickets',
        colorByPoint: true,
        data: brandsData
      }];

      var json = {};
      json.chart = chart;
      json.title = title;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.plotOptions = plotOptions;
      json.tooltip = tooltip;
      json.credits = credits;
      json.series = series;
      $('#failures').highcharts(json);
    }
  });
}

function getDataReportUser() {
  $.ajax({
    url: 'report-users',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val()}
  })
  .done(function(data)
  {
    $("#tsv-users").empty();
    var tsv = "Respuesta Total\n";
    var total = 0;
    $.each(data, function (index, value){
      tsv += value.name + " \t" + value.total + "%\n";
      if(value.total) {
        total = parseFloat(total) + parseFloat(value.total);
      }
    });
    $("#tsv-users").append(tsv);
    showReportUser(total);
  });
}

function showReportUser(total) {
  Highcharts.data({
    csv: document.getElementById('tsv-users').innerHTML,
    itemDelimiter: '\t',
    parsed: function (columns) {
      var brands = {}, brandsData = [], versions = {}, drilldownSeries = [];

      // Parse percentage strings
      columns[1] = $.map(columns[1], function (value) {
        if (value.indexOf('%') === value.length - 1) {
          value = parseFloat(value);
        }
        return value;
      });
      $.each(columns[0], function (i, name) {
        var brand, version;

        if (i > 0) {
          // Remove special edition notes
          name = name.split(' -')[0];

          // Split into brand and version
          version = name.match(/([0-9]+[\.0-9x]*)/);

          if (version) {
            version = version[0];
          }
          brand = name.replace(version, '');

          // Create the main data
          if (!brands[brand]) {
            brands[brand] = columns[1][i];
          } else {
            brands[brand] += columns[1][i];
          }

          // Create the version data
          if (version !== null) {
            if (!versions[brand]) {
              versions[brand] = [];
            }
            versions[brand].push(['v' + version, columns[1][i]]);
          }
        }
      });
      $.each(brands, function (name, y) {
        brandsData.push({
          name: name,
          y: y,
          drilldown: versions[name] ? name : null
        });
      });
      var chart = {
        type: 'column'
      };
      var title = {
        text: 'Usuarios que más tickets levantan'
      };
      var xAxis = {
        type: 'category'
      };
      var yAxis = {
        title: {
          text: 'Total de tickets'
        }
      };
      var plotOptions = {
        column: {
          allowPointSelect: false,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '{point.y}'
          },
          showInLegend: true
        },
        series: {
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '12px',
              fontWeight: 'normal',
              fill: '#f8f9fa',
              cursor: 'pointer'
            },
            softConnector: true
          },
          point: {
            events: {
              click: function () {
                var filter =  '';
                try {
                  filter = this.options.name;
                } catch (e) {
                }
                if(filter != '') {
                  showDatail(filter, 'Users');
                }
              }
            }
          }
        }
      };
      var tooltip = {
        headerFormat: '<span style = "font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style = "color:{point.color}">{point.name}</span>: <b>{point.y}</b> de un total de ' + total
      };
      var credits = {
        enabled: false
      };
      var series = [{
        name: 'Usuarios',
        colorByPoint: true,
        data: brandsData
      }];

      var json = {};
      json.chart = chart;
      json.title = title;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.plotOptions = plotOptions;
      json.tooltip = tooltip;
      json.credits = credits;
      json.series = series;
      $('#users').highcharts(json);
    }
  });
}

function getDataReportDay() {
  $.ajax({
    url: 'report-days',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val()}
  })
  .done(function(data)
  {
    $("#tsv-days").empty();
    var tsv = "Respuesta Total\n";
    var total = 0;
    $.each(data, function (index, value){
      tsv += value.name + " \t" + value.total + "%\n";
      if(value.total) {
        total = parseFloat(total) + parseFloat(value.total);
      }
    });
    $("#tsv-days").append(tsv);
    showReportDay(total);
  });
}

function showReportDay(total) {
  Highcharts.data({
    csv: document.getElementById('tsv-days').innerHTML,
    itemDelimiter: '\t',
    parsed: function (columns) {
      var brands = {}, brandsData = [], versions = {}, drilldownSeries = [];

      // Parse percentage strings
      columns[1] = $.map(columns[1], function (value) {
        if (value.indexOf('%') === value.length - 1) {
          value = parseFloat(value);
        }
        return value;
      });
      $.each(columns[0], function (i, name) {
        var brand, version;

        if (i > 0) {
          // Remove special edition notes
          name = name.split(' -')[0];

          // Split into brand and version
          version = name.match(/([0-9]+[\.0-9x]*)/);

          if (version) {
            version = version[0];
          }
          brand = name.replace(version, '');

          // Create the main data
          if (!brands[brand]) {
            brands[brand] = columns[1][i];
          } else {
            brands[brand] += columns[1][i];
          }

          // Create the version data
          if (version !== null) {
            if (!versions[brand]) {
              versions[brand] = [];
            }
            versions[brand].push(['v' + version, columns[1][i]]);
          }
        }
      });
      $.each(brands, function (name, y) {
        brandsData.push({
          name: name,
          y: y,
          drilldown: versions[name] ? name : null
        });
      });

      var chart = {
        type: 'column'
      };
      var title = {
        text: 'Tickets por estatus'
      };
      var xAxis = {
        type: 'category'
      };
      var yAxis = {
        title: {
          text: 'Total de tickets'
        }
      };
      var plotOptions = {
        column: {
          allowPointSelect: false,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '{point.y}'
          },
          showInLegend: true
        },
        series: {
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '12px',
              fontWeight: 'normal',
              fill: '#f8f9fa',
              cursor: 'pointer'
            },
            softConnector: true
          },
          point: {
            events: {
              click: function () {
                var filter =  '';
                try {
                  filter = this.options.name;
                } catch (e) {
                }
                if(filter != '') {
                  showDatail(filter, 'Days');
                }
              }
            }
          }
        }
      };
      var tooltip = {
        headerFormat: '<span style = "font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style = "color:{point.color}">{point.name}</span>: <b>{point.y}</b> de un total de ' + total
      };
      var credits = {
        enabled: false
      };
      var series = [{
        name: 'Status',
        colorByPoint: true,
        data: brandsData
      }];

      var json = {};
      json.chart = chart;
      json.title = title;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.plotOptions = plotOptions;
      json.tooltip = tooltip;
      json.credits = credits;
      json.series = series;
      $('#days').highcharts(json);
    }
  });
}

function getDataReportAreaSolution() {
  $.ajax({
    url: 'report-areas-solution',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val()}
  })
  .done(function(data)
  {
    $("#tsv-areas-solution").empty();
    var tsv = "Respuesta Total\n";
    var total = 0;
    $.each(data, function (index, value){
      tsv += value.name + " \t" + value.total + "%\n";
      if(value.total) {
        total = parseFloat(total) + parseFloat(value.total);
      }
    });
    $("#tsv-areas-solution").append(tsv);
    showReportAreaSolution(total);
  });
}

function showReportAreaSolution(total) {
  Highcharts.data({
    csv: document.getElementById('tsv-areas-solution').innerHTML,
    itemDelimiter: '\t',
    parsed: function (columns) {
      var brands = {}, brandsData = [], versions = {}, drilldownSeries = [];

      // Parse percentage strings
      columns[1] = $.map(columns[1], function (value) {
        if (value.indexOf('%') === value.length - 1) {
          value = parseFloat(value);
        }
        return value;
      });
      $.each(columns[0], function (i, name) {
        var brand, version;

        if (i > 0) {
          // Remove special edition notes
          name = name.split(' -')[0];

          // Split into brand and version
          version = name.match(/([0-9]+[\.0-9x]*)/);

          if (version) {
            version = version[0];
          }
          brand = name.replace(version, '');

          // Create the main data
          if (!brands[brand]) {
            brands[brand] = columns[1][i];
          } else {
            brands[brand] += columns[1][i];
          }

          // Create the version data
          if (version !== null) {
            if (!versions[brand]) {
              versions[brand] = [];
            }
            versions[brand].push(['v' + version, columns[1][i]]);
          }
        }
      });
      $.each(brands, function (name, y) {
        brandsData.push({
          name: name,
          y: y,
          drilldown: versions[name] ? name : null
        });
      });
      var chart = {
        type: 'column'
      };
      var title = {
        text: 'Áreas com más tickets dirigidos'
      };
      var xAxis = {
        type: 'category'
      };
      var yAxis = {
        title: {
          text: 'Total de tickets'
        }
      };
      var plotOptions = {
        column: {
          allowPointSelect: false,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '{point.y}'
          },
          showInLegend: true
        },
        series: {
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '12px',
              fontWeight: 'normal',
              fill: '#f8f9fa',
              cursor: 'pointer'
            },
            softConnector: true
          },
          point: {
            events: {
              click: function () {
                var filter =  '';
                try {
                  filter = this.options.name;
                } catch (e) {
                }
                if(filter != '') {
                  showDatail(filter, 'AreaSolutions');
                }
              }
            }
          }
        }
      };
      var tooltip = {
        headerFormat: '<span style = "font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style = "color:{point.color}">{point.name}</span>: <b>{point.y}</b> de un total de ' + total
      };
      var credits = {
        enabled: false
      };
      var series = [{
        name: 'Áreas',
        colorByPoint: true,
        data: brandsData
      }];
      var json = {};
      json.chart = chart;
      json.title = title;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.plotOptions = plotOptions;
      json.tooltip = tooltip;
      json.credits = credits;
      json.series = series;
      $('#areas-solution').highcharts(json);
    }
  });
}

function getDataReportUserSolution() {
  $.ajax({
    url: 'report-users-solution',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val()}
  })
  .done(function(data)
  {
    $("#tsv-users-solution").empty();
    var tsv = "Respuesta Total\n";
    var total = 0;
    $.each(data, function (index, value){
      tsv += value.name + " \t" + value.total + "%\n";
      if(value.total) {
        total = parseFloat(total) + parseFloat(value.total);
      }
    });
    $("#tsv-users-solution").append(tsv);
    showReportUserSolution(total);
  });
}

function showReportUserSolution(total) {
  Highcharts.data({
    csv: document.getElementById('tsv-users-solution').innerHTML,
    itemDelimiter: '\t',
    parsed: function (columns) {
      var brands = {}, brandsData = [], versions = {}, drilldownSeries = [];

      // Parse percentage strings
      columns[1] = $.map(columns[1], function (value) {
        if (value.indexOf('%') === value.length - 1) {
          value = parseFloat(value);
        }
        return value;
      });
      $.each(columns[0], function (i, name) {
        var brand, version;

        if (i > 0) {
          // Remove special edition notes
          name = name.split(' -')[0];

          // Split into brand and version
          version = name.match(/([0-9]+[\.0-9x]*)/);

          if (version) {
            version = version[0];
          }
          brand = name.replace(version, '');

          // Create the main data
          if (!brands[brand]) {
            brands[brand] = columns[1][i];
          } else {
            brands[brand] += columns[1][i];
          }

          // Create the version data
          if (version !== null) {
            if (!versions[brand]) {
              versions[brand] = [];
            }
            versions[brand].push(['v' + version, columns[1][i]]);
          }
        }
      });
      $.each(brands, function (name, y) {
        brandsData.push({
          name: name,
          y: y,
          drilldown: versions[name] ? name : null
        });
      });
      var chart = {
        type: 'column'
      };
      var title = {
        text: 'Usuarios que más tickets atienden'
      };
      var xAxis = {
        type: 'category'
      };
      var yAxis = {
        title: {
          text: 'Total de tickets'
        }
      };
      var plotOptions = {
        column: {
          allowPointSelect: false,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '{point.y}'
          },
          showInLegend: true
        },
        series: {
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '12px',
              fontWeight: 'normal',
              fill: '#f8f9fa',
              cursor: 'pointer'
            },
            softConnector: true
          },
          point: {
            events: {
              click: function () {
                var filter =  '';
                try {
                  filter = this.options.name;
                } catch (e) {
                }
                if(filter != '') {
                  showDatail(filter, 'UserSolutions');
                }
              }
            }
          }
        }
      };
      var tooltip = {
        headerFormat: '<span style = "font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style = "color:{point.color}">{point.name}</span>: <b>{point.y}</b> de un total de ' + total
      };
      var credits = {
        enabled: false
      };
      var series = [{
        name: 'Usuarios',
        colorByPoint: true,
        data: brandsData
      }];
      var json = {};
      json.chart = chart;
      json.title = title;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.plotOptions = plotOptions;
      json.tooltip = tooltip;
      json.credits = credits;
      json.series = series;
      $('#users-solution').highcharts(json);
    }
  });
}

function getDataReportTime() {
  $.ajax({
    url: 'report-time',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val()}
  })
  .done(function(data)
  {
    $("#tsv-time").empty();
    var tsv = "Respuesta Total\n";
    var total = 0;
    $.each(data, function (index, value){
      tsv += value.name + " \t" + value.total + "%\n";
      if(value.total) {
        total = parseFloat(total) + parseFloat(value.total);
      }
    });
    $("#tsv-time").append(tsv);
    showReportTime(total);
  });
}

function showReportTime(total) {
  Highcharts.setOptions({
	lang: {
  	thousandsSep: ','
  }
})

  Highcharts.data({
    csv: document.getElementById('tsv-time').innerHTML,
    itemDelimiter: '\t',
    parsed: function (columns) {
      var brands = {}, brandsData = [], versions = {}, drilldownSeries = [];

      // Parse percentage strings
      columns[1] = $.map(columns[1], function (value) {
        if (value.indexOf('%') === value.length - 1) {
          value = parseFloat(value);
        }
        return value;
      });
      $.each(columns[0], function (i, name) {
        var brand, version;

        if (i > 0) {
          // Remove special edition notes
          name = name.split(' -')[0];

          // Split into brand and version
          version = name.match(/([0-9]+[\.0-9x]*)/);

          if (version) {
            version = version[0];
          }
          brand = name.replace(version, '');

          // Create the main data
          if (!brands[brand]) {
            brands[brand] = columns[1][i];
          } else {
            brands[brand] += columns[1][i];
          }

          // Create the version data
          if (version !== null) {
            if (!versions[brand]) {
              versions[brand] = [];
            }
            versions[brand].push(['v' + version, columns[1][i]]);
          }
        }
      });
      $.each(brands, function (name, y) {
        brandsData.push({
          name: name,
          y: y,
          drilldown: versions[name] ? name : null
        });
      });
      var chart = {
        type: 'column'
      };
      var title = {
        text: 'Tiempo promedio de atención de tickets por área'
      };
      var xAxis = {
        type: 'category'
      };
      var yAxis = {
        title: {
          text: 'Tiempo promedio en minutos'
        }
      };
      var plotOptions = {
        column: {
          allowPointSelect: false,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '{point.y:.,.0f}'
          },
          showInLegend: true
        },
        series: {
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '12px',
              fontWeight: 'normal',
              fill: '#f8f9fa',
              cursor: 'pointer'
            },
            softConnector: true
          },
          point: {
            events: {
              click: function () {
                var filter =  '';
                try {
                  filter = this.options.name;
                } catch (e) {
                }
                if(filter != '') {
                  showDatail(filter, 'AreaSolutions');
                }
              }
            }
          }
        }
      };
      var tooltip = {
        headerFormat: '<span style = "font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style = "color:{point.color}">{point.name}</span>: <b>{point.y:.,.0f}</b> minutos'
      };
      var credits = {
        enabled: false
      };
      var series = [{
        name: 'Áreas',
        colorByPoint: true,
        data: brandsData
      }];
      var json = {};
      json.chart = chart;
      json.title = title;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.plotOptions = plotOptions;
      json.tooltip = tooltip;
      json.credits = credits;
      json.series = series;
      $('#time').highcharts(json);
    }
  });
}

function showDatail(filter, type) {
  $.ajax({
    url: 'report-detail',
    type: 'GET',
    dataType: 'JSON',
    data: {fechaInicio: $('#fecha-inicio').val(), fechaFin: $('#fecha-fin').val(), filter: filter, type: type},
  })
  .done(function(data)
  {
    dataSet = [];
    $.each(data, function (index, value){
        console.log(value.fecha_solicitud);
      var created_at = value.fecha_solicitud ? moment(value.fecha_solicitud).format('YYYY/MM/DD HH:mm:ss'): '';
      var fechaSolucion = value.fecha_fin ? moment(value.fecha_fin).format('YYYY/MM/DD HH:mm:ss') : '';
      console.log(created_at);
      console.log(fechaSolucion);
      dataSet.push([index+1,value.area_solicita, value.usuario, value.falla, value.description, value.solution, value.observations, value.status, created_at, fechaSolucion, value.area_atiende, value.responsable]);
    });
    $("#datos-table").html('<table id="deatil-table" class="table table-bordered table-sm" style="width:100%"></table>');
    var table = $('#deatil-table').DataTable({
      language: {
        'url': '../js/spanish.json',
      },
      responsive: true,
      processing: true,
      data: dataSet,
      columns: [
        { title: "#" },
        { title: "Área solicita" },
        { title: "Usuario" },
        { title: "Falla" },
        { title: "Descripción"},
        { title: "Solución"},
        { title: "Observaciones"},
        { title: 'Estatus'},
        { title: 'Fecha solicitud'},
        { title: 'Fecha solución'},
        { title: 'Área atiende'},
        { title: 'Usario atiende'}
      ],
      dom: 'Bfrtip',
      buttons: ['excel', 'pdf']
    });
    if($(window).width() < 576){
      $('#deatil-table').removeClass('nowrap');
    }
    else {
      $('#deatil-table').addClass('nowrap');
    }

    $('#modal-datos').modal('show');
  });
}

$('#modal-datos').on('shown.bs.modal', function (e) {
  $("#deatil-table").DataTable()
  .columns.adjust()
  .responsive.recalc();
})
