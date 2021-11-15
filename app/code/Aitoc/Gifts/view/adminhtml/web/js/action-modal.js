define([
    "jquery",
    "jquery/ui",
    'Magento_Ui/js/modal/modal'
], function ($, ui, modal) {

    $.widget('mage.aitocActionModal', {
        options: {
            actions: {},
            providers: [],
            successClass: 'success',
            failClass: 'fail',
            loadingClass: 'loading'
        },

        elements: {
        },

        detailsElement: null,

        _create: function () {
            var self = this;
        },

        check: function () {
        },
        
        requestActionContent: function () {
            $.ajax(this.options.actions.actionCreateUrl, {
                data: {},
                dataType: "json",
                type: 'GET',
                success: function (response) {
                    // widget.checkTrigger.addClass(
                    //     response.success ? widget.options.successClass : widget.options.failClass
                    // );
                    // widget.checkTrigger.children('span').html(response.message);
                    //
                    // if (response.details) {
                    //     widget.detailsElement.find('[data-role=message]').html(
                    //         response.details.error_message + '<br/><br/>' + response.details.trace
                    //     );
                    //     widget.detailsElement.addClass('collapsed');
                    //     widget.detailsElement.show();
                    // }
                },
                complete: function () {
                    // widget.checkTrigger.removeClass(widget.options.loadingClass);
                },
                beforeSend: function () {
                    // widget.checkTrigger.addClass(widget.options.loadingClass);
                    // widget.checkTrigger.removeClass(widget.options.failClass);
                    // widget.checkTrigger.removeClass(widget.options.successClass);
                }
            });
        },

        openModal: function () {
            var options = {
                type: 'slide',
                responsive: true,
                innerScroll: true,
                title: 'popup modal title',
                buttons: [{
                    text: $.mage.__('Continue'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };

            var popup = modal(options, $('#popup-modal'));

            $('#popup-modal').modal('openModal');
        }
    });

    return $.mage.aitocActionModal;
});
