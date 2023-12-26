define([
    'jquery',
    'ko',
    'uiComponent'
 ], function ($, ko, Component) {
    'use strict';
    return Component.extend({
        initialize: function () {
            //initialize parent Component
            this._super();
            this.qty = ko.observable(this.defaultQty);
        },
        decreaseQty: function() {
            var newQty = this.qty() - 1;
            if (newQty < 1) {
                newQty = 1;
            }
            this.qty(newQty);

            $('.decreaseQty').click(function() {
                var totalQty = parseInt($(".total-qtys").val());
                var enteredQty = parseInt($(".entered-qty").val());
                var button = document.getElementById('increaseQty');
                if (enteredQty < totalQty) {
                    button.disabled = false;
                }
            });
        },

        // increaseQty: function() {
        //     var newQty = this.qty() + 1;
        //     this.qty(newQty);
        //     $('.increaseQty').click(function() {
        //         var totalQty = $(".total-qtys").val();
        //         var totalQty = totalQty.split('_');
        //         if (totalQty.length == 1) {
        //             var enteredQty = parseInt($(".entered-qty").val());
        //             var button = document.getElementById('increaseQty');
        //             if (enteredQty >= totalQty) {
        //                 button.disabled = true;
        //             } else {
        //                 button.disabled = false;
        //             }
        //         }
        //     });
        // }
        increaseQty: function() {
            var newQty = this.qty() + 1;
            var resetQty = $(".reset-qty").val();
            if (resetQty==1) {
                $(".entered-qty").val(1);
                $(".reset-qty").val(0);
                var newQty = 1;
            }
            this.qty(newQty);

            $('.increaseQty').click(function() {
                var totalQty = $(".total-qtys").val();
                var totalQty = totalQty.split('_');
                if (totalQty.length == 1) {
                    var enteredQty = parseInt($(".entered-qty").val());
                    var button = document.getElementById('increaseQty');
                    if (enteredQty >= totalQty) {
                        button.disabled = true;
                    } else {
                        button.disabled = false;
                    }
                }
            });
        }
    });
});
