/*
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery"], function ($) {
    "use strict";
    return {
        waitFor: function (elt, callback) {
            var initializer = null;
            initializer = setInterval(function () {
                if ($(elt).length > 0) {
                    setTimeout(callback, 500);
                    clearInterval(initializer);
                }
            }, 200);
        },
        getstate: function (selectElement, reloadStateUrl) {
            $("#state").html("Searching…");
            $.ajax({
                url: reloadStateUrl + selectElement.value,
                type: "GET",
                showLoader: true,
                data: {},
                success: function (data) {
                    $("#state").html(data);
                }
            });
        },
        initializeGMap: function () {
            var latitude = document.getElementById("latitude").value;
            var longitude = document.getElementById("longitude").value;
            if (latitude === "") {
                latitude = "48.856951";
            }
            if (longitude === "") {
                longitude = "2.346868";
            }
            var zoom = 10;
            var LatLng = new google.maps.LatLng(latitude, longitude);
            var mapOptions = {
                zoom: zoom,
                center: LatLng,
                panControl: false,
                scaleControl: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            var map = new google.maps.Map(document.getElementById("map"), mapOptions);
            var marker = new google.maps.Marker({
                position: LatLng,
                map: map,
                title: "Drag Me!",
                draggable: true
            });
            google.maps.event.addListener(marker, "dragend", function (marker) {
                var latLng = marker.latLng;
                document.getElementById("longitude").value = latLng.lng().toFixed(6);
                document.getElementById("latitude").value = latLng.lat().toFixed(6);
            });
        },
        initializeHours: function (id) {
            this.waitFor("#hours", function () {
                if ($("#hours").val() === "") {
                    $("#hours").val("{}");
                }
                var data = JSON.parse($('#hours').val());
                
                for (var day in data) {
                    $("#" + day).prop("checked", true);
                    var time = data[day];
                    $("#" + day + "_open").val(time.from);
                    $("#" + day + "_close").val(time.to);
                    if (typeof time.lunch_from !== "undefined") {
                        $("#" + day + "_lunch").prop("checked", true);    
                        $("#" + day + "_lunch_open").val(time.lunch_from);
                        $("#" + day + "_lunch_close").val(time.lunch_to);
                    } else {
                        $("#" + day + "_lunch").prop("checked", false);
                    }
                }
                $('.' + id + "_day").each(function () {
                    if (!$(this).prop("checked")) {
                        $(this).parent().parent().find("SELECT")[0].disabled = true;
                        $(this).parent().parent().find("SELECT")[1].disabled = true;
                    }
                });
                $('.' + id + "_lunch").each(function () {
                    $(this).prop("disabled",!$("#"+$(this).val()).prop("checked"));
                    if (!$(this).prop("checked")) {
                        $(this).parent().parent().find("SELECT")[0].disabled = true;
                        $(this).parent().parent().find("SELECT")[1].disabled = true;
                    }
                });
            }.bind(this));
        },
        activeField: function (e, id) {
            var enabled = $(e).prop("checked");
            $(e).parent().parent().find("SELECT")[0].disabled = !enabled;
            $(e).parent().parent().find("SELECT")[1].disabled = !enabled;

            var lunch = $("#"+$(e).val()+"_lunch");
            lunch.prop("checked",false);
            lunch.prop("disabled",!enabled);
            lunch.parent().parent().find("SELECT")[0].disabled = true;
            lunch.parent().parent().find("SELECT")[1].disabled = true;
            this.summary(id);
        },
        activeFieldLunch: function (e, id) {
            $(e).parent().parent().find("SELECT")[0].disabled = !$(e).prop("checked");
            $(e).parent().parent().find("SELECT")[1].disabled = !$(e).prop("checked");
            this.summary(id);
        },
        summary: function (id) {
            var hours = {};
            $('.' + id + "_day").each(function (e) {
                if ($(this).prop("checked")) {
                    hours[$(this).val()] = {
                        from: $(this).parent().parent().find("SELECT")[0].value,
                        to: $(this).parent().parent().find("SELECT")[1].value
                    };
                }
            });
            $('.' + id + "_lunch").each(function (e) {
                if ($(this).prop("checked")) {
                    if (typeof hours[$(this).val()] === "undefined") {
                        hours[$(this).val()] =  {};
                    }
                    hours[$(this).val()]['lunch_from'] = $(this).parent().parent().find("SELECT")[0].value;
                    hours[$(this).val()]['lunch_to'] = $(this).parent().parent().find("SELECT")[1].value;
                }
            });
            $("#hours").val(Object.toJSON(hours));
        }
    };
});