define(['jquery', 'Magento_Ui/js/modal/modal'], function ($) {
    return {
        /**
         * Is the content of the modal window loaded?
         */
        loaded: false,
        /**
         * The "choose a store" button selector
         */
        chooseStoreBtn: null,
        /**
         * Widget Configuration
         */
        config: null,
        /**
         * The modal window selector
         */
        modalPopup: null,
        /**
         * The modal window title
         */
        modalTitle: null,
        /**
         * The Point Of Sale obj
         */
        posObj: null,
        /**
         * Name of the cookie to store the preferred store information
         */
        preferredStoreCookie: "preferred_store",
        /**
         * Preferred store information
         */
        preferredStore: null,
        /**
         * Automatically select the nearest store if the customer doesn't have select a preferred store yet.
         */
        autoSelect: false,
        /**
         * Update preferred stores at the customer level url
         */
        updatePreferredStoreUrl: "",

        /**
         * Initializing observers
         */
        initObservers: function () {

            if (this.isWidget) {
                // open the "Choose preferred store" window
                $(this.chooseStoreBtn).on('click', function () {
                    // if btn is disabled => nothin happens
                    if ($(this.chooseStoreBtn).find('.action.primary.disabled').hasClass('disabled')) {
                        return;
                    }

                    if (!this.loaded) {
                        // if the window has not been loaded a first time
                        // load the content of the window
                        $(this.modalPopup).load(this.config.modalPopup.contentUrl, {"canSelectPreferredStore": true}, function () {
                            // once loaded, remove the gif loader
                            $(this.modalPopup).removeClass('preferred-store-loading');
                        }.bind(this));
                        this.loaded = true;
                    }

                    // open the modal window
                    $(this.modalPopup).modal("openModal");

                }.bind(this));
            } else {
                $(this.chooseStoreBtn).remove();
            }

            // Choose a store
            $(document).on('click', '.choose_preferred_store', function (evt) {
                var storeId = $(evt.target).attr('id').replace('preferred_store_', '');
                var index = this.posObj.getStoreIndexById(storeId);
                // remove "preferred" class on all POS
                var elts = $('.preferred-store-popup .place.preferred');
                elts.each(function (i) {
                    $(elts[i]).removeClass('preferred');
                });
                // add "preferred" class on the selected POS
                $(".tools-buttons #" + evt.target.id).parents('div.place').addClass('preferred');


                var data = {
                    "id": this.posObj.places[index].id,
                    "name": this.posObj.places[index].name
                }
                this.setCookie(this.preferredStoreCookie, JSON.stringify(data));
                this.preferredStore = data;

                $('.preferred-store-widget .preferred-store-selected').html(this.preferredStore.name);
                $(document).trigger('preferred-store-selected', [index]);
            }.bind(this));


            if (this.autoSelect && this.preferredStore === null) {
                $(document).on('nearest-store_found', function (evt, pos, index) {
                    var data = {
                        "id": pos.id,
                        "name": pos.name
                    }
                    this.setCookie(this.preferredStoreCookie, JSON.stringify(data));
                    this.preferredStore = data;
                    $('.preferred-store-widget .preferred-store-selected').html(this.preferredStore.name);
                    $(document).trigger('preferred-store-selected', [index]);
                }.bind(this));
            }

            // POS list update
            $(document).on('pos_list_updated', function (evt, posObj) {
                this.posObj = posObj;
            }.bind(this));


            $(document).on('preferred-store-selected', function () {
                $.ajax({
                    url: this.updatePreferredStoreUrl,
                    type: 'post',
                    data: {store: this.preferredStore},
                    showLoader: false,
                    success: function (data) {

                    },
                    error: function (data) {
                        alert(data);
                    }
                });
            }.bind(this));

        },

        /**
         * Something like the constructor
         * @param config
         * @param chooseStoreBtn
         * @constructor
         */
        'Wyomind_PickupAtStore/js/preferred-store': function (config, chooseStoreBtn) {

            this.isWidget = config.widget;
            this.config = config;
            this.chooseStoreBtn = chooseStoreBtn;
            this.modalPopup = config.modalPopup.element;
            this.modalTitle = config.modalPopup.title;
            this.autoSelect = config.autoSelect;
            this.updatePreferredStoreUrl = config.updatePreferredStoreUrl;

            // initialize the modal window
            if (this.isWidget) {
                $(this.modalPopup).modal({
                    'type': "popup",
                    'title': this.modalTitle,
                    'modalClass': "",
                    buttons: [{
                        text: 'Close',
                        class: '',
                        click: function () {
                            this.closeModal();
                        }
                    }]
                });
            }


            // => displaying the selected store name (if selected)
            this.preferredStore = $.parseJSON(this.getCookie(this.preferredStoreCookie));

            this.initObservers();
            if (!this.isWidget) {
                $(this.modalPopup).css({"display":"block"});
                $(this.modalPopup).load(this.config.modalPopup.contentUrl, {"canSelectPreferredStore": true}, function () {
                    $(this.modalPopup).removeClass('preferred-store-loading');
                    this.loaded = true;
                }.bind(this));
            }

            if (typeof this.preferredStore !== "undefined" && this.preferredStore !== null) {
                let name = this.preferredStore.name;
                name = name.replace(new RegExp(/\+/g), ' ');
                $('.preferred-store-widget .preferred-store-selected').html(name);
            } else {
                $('.preferred-store-widget .preferred-store-selected').html(this.config.labels.noStoreSelected);
            }

            // => enabling the "choose a store" button
            $(this.chooseStoreBtn).find('.action.primary.disabled').removeClass('disabled');

            $(document).trigger('preferred-store-loaded');

        },

        /**
         * Set a cookie
         * @param cname
         * @param cvalue
         * @param exdays
         */
        setCookie: function (cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        },
        /**
         * Get a cookie
         * @param cname
         * @returns {string}
         */
        getCookie: function (cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
    };
});