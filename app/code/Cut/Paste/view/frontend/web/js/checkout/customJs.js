define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'mage/url'
    ],
    function (ko, $, Component,url) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Cut_Paste/checkout/customCheckbox'
            },
            initObservable: function (e) {

                var self = this;
                

                this.textValue = ko.observable();
                this.radioValue = ko.observable('1');
                this.rawDate = ko.observable();
                var easyMan = "";
                var sorryBudy = "";
                var thanksBuddy = "";

                this.submitForm = ko.observable();
                this.submitFormData = function(data,event) {
                    easyMan=this.textValue();
                    sorryBudy=this.radioValue();
                    thanksBuddy=this.rawDate();
                    var linkUrls  = url.build('module/checkout/saveInQuote');
                    //console.log('easyMan',easyMan);
                     $.ajax({
                        showLoader: true,
                        url: linkUrls,
                        data: {checkVal : easyMan,orderdate:thanksBuddy,ordertime:sorryBudy},
                        type: "POST",
                        dataType: 'json'
                    }).done(function (data) {
                        console.log('success');
                        $('#testing').empty();
                        $('#testing').append('<div>order save success fully</div>');
                    });
                   this.submitForm('');
                }

                

                return this;
            }
        });
    }
);