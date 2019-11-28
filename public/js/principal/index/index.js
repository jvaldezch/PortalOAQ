/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    
    window.traficos = function () {
        return $.ajax({url: '/trafico/crud/mis-traficos',
            success: function (res) {
                if (res.success === true) {
                    for (var i = 0; i < res.results.length; i++) {
                        if (i < 16) {
                            var row = res.results[i];
                            $('#myTraffics').append('<tr><td style="padding: 2px 4px"><a href="/trafico/index/editar-trafico?id=' + row.id + '">' + row.referencia + '</a></td></tr>');
                        } else {
                            $('#myTraffics').append('<tr><td style="padding: 2px 4px"><a href="/trafico/index/traficos">Más tráficos ...</a></td></tr>');
                            break;
                        }
                    }
                    return true;
                }
            }
        });
    };
    
    traficos();
    
    
});

$(document.body).on('click', '.btn-menu', function (ev) {
    ev.preventDefault();
    window.location = $(this).data("url");
});

function stats() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/principal/get/estadisticas');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var res = JSON.parse(xhr.responseText);
            if (res.success === true) {
                for(var i in res.stats) {
                    var v = res.stats[i];
                    var tg = document.querySelector("span#" + v["key"]);
                    if (tg != undefined) {
                        tg.innerHTML = v["value"];
                    }
                }   
            } else {
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "450px", useBootstrap: false});
            }
        }
        else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
    };
    xhr.send();
}

document.addEventListener("DOMContentLoaded", function(event) { 
    stats();
    draw();
    drawc();
    update();
    updatec();
    window.setInterval(function(){
        stats();
        update();
    }, 60000);
});

var n = 8;  // The number of series.

var svg = d3.select("svg#indicators");
var margin = {top: 10, right: 10, bottom: 40, left: 30};
var width = +svg.attr("width") - margin.left - margin.right;
var height = +svg.attr("height") - margin.top - margin.bottom;
var g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var x = d3.scaleBand()
    .domain([0,1,2,3,4,5,6,7,8,9,10,11,12,13])
    .range([0, width])
    .padding(0.08);

var y = d3.scaleLinear()
    .range([height, 0]);

var xAxis = d3.axisBottom(x);
var yAxis = d3.axisLeft(y);

var color = d3.scaleOrdinal() 
    .domain(["COVES", "EDOCS", "Expedientes", "Pagados", "Liberados", "Transmitidos", "Firmas", "Pagos"])
    .range(["#0177c1", "#0587d8" , "#0ecad1", "#45abcd", "#75cd45", "#feaf20", "#e39a17", "#c78918"]);

function update() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/principal/get/live-stats');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var res = JSON.parse(xhr.responseText);
            if (res.success === true) {
                var yz = res.stats;
                updateGraph(yz);
            }
        }
        else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
    };
    xhr.send();
}

function updateGraph(yz) {
    var y01z = d3.stack().keys(d3.range(n))(d3.transpose(yz));
    var yMax = d3.max(yz, function(y) { return d3.max(y); });

    var series = g.selectAll(".series")
        .remove()
        .exit()
        .data(y01z)
        .enter().append("g")
        .attr("fill", function(d, i) { return color(i); });

    y.domain([0, yMax]).range([height, 0]);
    svg.select('.axis--y')
        .call(yAxis);

    var rect = series.selectAll("rect")
        .data(function(d) { 
            return d; 
        })
        .enter().append("rect")
        .attr("x", function(d, i) { 
            return x(i);
        })
        .attr("y", height)
        .attr("width", x.bandwidth())
        .attr("height", 0);

    rect.transition()
        .duration(500)
        .delay(function(d, i) {
            return i * 10;
        })
        .attr("x", function(d, i) {
            var xd = x(i) + x.bandwidth() / n * this.parentNode.__data__.key + 1;
            return xd;
        })
        .attr("width", x.bandwidth() / n)
        .transition()
        .attr("y", function(d) { 
            return y(d[1] - d[0]); 
        })
        .attr("height", function(d) { 
            return y(0) - y(d[1] - d[0]);
        });
}

function draw() {       

    var series = g.selectAll(".series")
      .enter().append("g")
        .attr("fill", function(d, i) { return color(i); });

    var rect = series.selectAll("rect")
      .data(function(d) { return d; })
      .enter().append("rect")
        .attr("x", function(d, i) { return x(i); })
        .attr("y", height)
        .attr("width", x.bandwidth())
        .attr("height", 0);

    rect.transition()
          .duration(500)
          .delay(function(d, i) { return i * 10; })
          .attr("x", function(d, i) { return x(i) + x.bandwidth() / n * this.parentNode.__data__.key; })
          .attr("width", x.bandwidth() / n)
        .transition()
          .attr("y", function(d) { return y(d[1] - d[0]); })
          .attr("height", function(d) { return y(0) - y(d[1] - d[0]); });

    g.append("g")
        .attr("class", "axis axis--x")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x)
            .tickSize(0)
            .tickPadding(2)
            .tickFormat(function(d) { 
                return d + 7;
            }));

    g.append("text")             
        .attr("transform", "translate(" + (width/2) + " ," + (height + margin.top + 15) + ")")
        .style("text-anchor", "middle")
        .style("font-size", "10px")
        .text("Hora");

    g.append("g")
        .attr("class", "axis axis--y")
        .call(d3.axisLeft(y)
            .tickSize(2)
            .tickPadding(4));

    g.append("text")
      .attr("transform", "rotate(-90)")
      .attr("y", 0 - (margin.left))
      .attr("x",0 - (height / 2))
      .attr("dy", "0.8em")
      .style("text-anchor", "middle")
      .style("font-size", "10px")
      .text("Cantidad");

    svg.append("text")
        .attr("transform","translate(105,7)")
        .style("font-size","10px")
        .attr("class", "legend")
        .text("Indicadores por hora");
}

var nc = 3;  // The number of series.

var svgc = d3.select("svg#customs");
var marginc = {top: 10, right: 10, bottom: 40, left: 30};
var widthc = +svgc.attr("width") - marginc.left - marginc.right;
var heightc = +svgc.attr("height") - marginc.top - marginc.bottom;
var gc = svgc.append("g").attr("transform", "translate(" + marginc.left + "," + marginc.top + ")");

var xc = d3.scaleBand()
    .domain([0,1,2,3,4,5,6,7,8,9,10,11,12,13])
    .range([0, widthc])
    .padding(0.08);

var yc = d3.scaleLinear()
    .range([heightc, 0]);

var xAxisc = d3.axisBottom(xc);
var yAxisc = d3.axisLeft(yc);

var colorc = d3.scaleOrdinal() 
    .domain(["3589-640 OPS", "3589-640", "3589-240"])
    .range(["#0177c1", "#0587d8", "#0ecad1"]);

function updatec() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/principal/get/live-stats-customs');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var res = JSON.parse(xhr.responseText);
            if (res.success === true) {
                var yz = res.stats;
                updateGraphc(yz);
            }
        }
        else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
    };
    xhr.send();
}

function updateGraphc(yz) {
    var y01z = d3.stack().keys(d3.range(nc))(d3.transpose(yz));
    var yMax = d3.max(yz, function(y) { return d3.max(y); });

    var series = gc.selectAll(".series")
        .remove()
        .exit()
        .data(y01z)
        .enter().append("g")
        .attr("fill", function(d, i) { return colorc(i); });

    yc.domain([0, yMax]).range([heightc, 0]);
    svgc.select('.axis--y')
        .call(yAxisc);

    var rect = series.selectAll("rect")
        .data(function(d) { return d; })
        .enter().append("rect")
        .attr("x", function(d, i) { 
            return xc(i);
        })
        .attr("y", heightc)
        .attr("width", xc.bandwidth())
        .attr("height", 0);

    rect.transition()
        .duration(500)
        .delay(function(d, i) { return i * 10; })
        .attr("x", function(d, i) {
            var xd = xc(i) + xc.bandwidth() / nc * this.parentNode.__data__.key + 1;
            return xd;
        })
        .attr("width", xc.bandwidth() / nc)
        .transition()
        .attr("y", function(d) { return yc(d[1] - d[0]); })
        .attr("height", function(d) { return yc(0) - yc(d[1] - d[0]); });
}

function drawc() {

    var seriesc = gc.selectAll(".series")
      .enter().append("g")
        .attr("fill", function(d, i) { return colorc(i); });

    var rectc = seriesc.selectAll("rect")
      .data(function(d) { return d; })
      .enter().append("rect")
        .attr("x", function(d, i) { return xc(i); })
        .attr("y", heightc)
        .attr("width", xc.bandwidth())
        .attr("height", 0);

    rectc.transition()
          .duration(500)
          .delay(function(d, i) { return i * 10; })
          .attr("x", function(d, i) { return xc(i) + xc.bandwidth() / nc * this.parentNode.__data__.key; })
          .attr("width", xc.bandwidth() / nc)
        .transition()
          .attr("y", function(d) { return yc(d[1] - d[0]); })
          .attr("height", function(d) { return yc(0) - yc(d[1] - d[0]); });

    gc.append("g")
        .attr("class", "axis axis--x")
        .attr("transform", "translate(0," + heightc + ")")
        .call(d3.axisBottom(xc)
            .tickSize(0)
            .tickPadding(2)
            .tickFormat(function(d) { 
                return d + 7;
            }));

    gc.append("text")             
        .attr("transform", "translate(" + (widthc/2) + " ," + (heightc + marginc.top + 15) + ")")
        .style("text-anchor", "middle")
        .style("font-size", "10px")
        .text("Hora");

    gc.append("g")
        .attr("class", "axis axis--y")
        .call(d3.axisLeft(yc)
            .tickSize(2)
            .tickPadding(4));

    gc.append("text")
      .attr("transform", "rotate(-90)")
      .attr("y", 0 - (marginc.left))
      .attr("x",0 - (heightc / 2))
      .attr("dy", "0.8em")
      .style("text-anchor", "middle")
      .style("font-size", "10px")
      .text("Cantidad");

    svgc.append("text")
        .attr("transform","translate(75,7)")
        .style("font-size","10px")
        .attr("class", "legend")
        .text("Tráficos liberados por hora x aduana");

    svgc.append("text")
        .attr("transform","translate(230,20)")
        .style("font-size","10px")
        .attr("class", "legend")
        .style("fill", "#0177c1")
        .attr("font-weight", "bold")
        .text("3589-640"); 
    svgc.append("text")
        .attr("transform","translate(230,30)")
        .style("font-size","10px")
        .attr("class", "legend")
        .style("fill", "#0587d8")
        .attr("font-weight", "bold")
        .text("3589-640 OPS"); 
    svgc.append("text")
        .attr("transform","translate(230,40)")
        .style("font-size","10px")
        .attr("class", "legend")
        .style("fill", "#0ecad1")
        .attr("font-weight", "bold")
        .text("3589-240"); 
}