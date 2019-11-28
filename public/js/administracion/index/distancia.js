/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function iniciarMapa(oLat, oLng, dLat, dLng) {
    var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer;
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 7,
        center: {lat: parseFloat(oLat), lng: parseFloat(oLng)}
    });
    directionsDisplay.setMap(map);

    var origin = new google.maps.LatLng(parseFloat(oLat),parseFloat(oLng));
    var destination = new google.maps.LatLng(parseFloat(dLat),parseFloat(dLng));
    calculateAndDisplayRoute(directionsService, directionsDisplay, origin, destination);
    
    $.post("/administracion/ajax/obtener-distancia", {origen: oLat + ',' + oLng, destino: dLat + ',' + dLng})
            .done(function (res) {
                if (res.success === true) {
                    $("#result").html("<strong>Distancia:</strong> " + res.distance.text + " <br><strong>tiempo de recorrido:</strong> " + res.time.text + "</p>");
                }
            });
}

function calculateAndDisplayRoute(directionsService, directionsDisplay, origin, destination) {
    directionsService.route({
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode.DRIVING
    }, function (response, status) {
        if (status === google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(response);
        } else {
            window.alert('Directions request failed due to ' + status);
        }
    });
    
}

function initMap(div, lat, lng, label) {
    var myLatLng = {lat: parseFloat(lat), lng: parseFloat(lng)};

    var map = new google.maps.Map(document.getElementById(div), {
        zoom: 11,
        center: myLatLng
    });

    var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
        title: label
    });
}

$(document).ready(function () {
    
    var oLat;
    var oLng;
    var dLat;
    var dLng;

    $(document.body).on("click", "#viewMap", function () {
        if(oLat && oLng && dLat && dLng) {
            iniciarMapa(oLat, oLng, dLat, dLng);
        }
    });
    
    $(document.body).on("change", "#origen #localidad", function () {
        $.post("/administracion/ajax/datos-localidad", {id: $(this).val()})
                .done(function (res) {
                    if (res.success === true) {
                        var obj = res.json;
                        oLat = obj.lat;
                        oLng = obj.lng;
                    }
                });
    });
    
    $(document.body).on("change", "#destino #localidad", function () {
        $.post("/administracion/ajax/datos-localidad", {id: $(this).val()})
                .done(function (res) {
                    if (res.success === true) {
                        var obj = res.json;
                        dLat = obj.lat;
                        dLng = obj.lng;
                    }
                });
    });

    $(document.body).on("change", "#origen #estado", function () {
        $("#origen #municipio")
                .removeAttr("disabled")
                .empty()
                .append('<option value="">---</option>');
        $("#origen #localidad")
                .attr("disabled", "disabled")
                .empty()
                .append('<option value="">---</option>');
        $.post("/administracion/ajax/obtener-municipios", {id: $(this).val()})
                .done(function (res) {
                    if (res.success === true) {
                        $.each(res.json, function (i, item) {
                            $("#origen #municipio").append($("<option>", {
                                value: item.id,
                                text: item.nombre.toUpperCase()
                            }));
                        });
                    }
                });
    });

    $(document.body).on("change", "#destino #estado", function () {
        $("#destino #municipio")
                .removeAttr("disabled")
                .empty()
                .append('<option value="">---</option>');
        $("#destino #localidad")
                .attr("disabled", "disabled")
                .empty()
                .append('<option value="">---</option>');
        $.post("/administracion/ajax/obtener-municipios", {id: $(this).val()})
                .done(function (res) {
                    if (res.success === true) {
                        $.each(res.json, function (i, item) {
                            $("#destino #municipio").append($("<option>", {
                                value: item.id,
                                text: item.nombre.toUpperCase()
                            }));
                        });
                    }
                });
    });

    $(document.body).on("change", "#origen #municipio", function () {
        $("#origen #localidad")
                .removeAttr("disabled")
                .empty()
                .append('<option value="">---</option>');
        $.post("/administracion/ajax/obtener-localidades", {id: $(this).val()})
                .done(function (res) {
                    if (res.success === true) {
                        $.each(res.json, function (i, item) {
                            $("#origen #localidad").append($("<option>", {
                                value: item.id,
                                text: item.nombre.toUpperCase()
                            }));
                        });
                    }
                });
    });

    $(document.body).on("change", "#destino #municipio", function () {
        $("#destino #localidad")
                .removeAttr("disabled")
                .empty()
                .append('<option value="">---</option>');
        $.post("/administracion/ajax/obtener-localidades", {id: $(this).val()})
                .done(function (res) {
                    if (res.success === true) {
                        $.each(res.json, function (i, item) {
                            $("#destino #localidad").append($("<option>", {
                                value: item.id,
                                text: item.nombre.toUpperCase()
                            }));
                        });
                    }
                });
    });

});
