(function ($) {

    "use strict";

    /**
     * Complex Skin implementation for module MainNavigation.
     *
     * @author Terrific Composer
     * @namespace Tc.Module.MainNavigation
     * @class Complex
     * @extends Tc.Module
     * @constructor
     */
    Tc.Module.MainNavigation.Complex = function (parent) {
        this.$ctx.parents(".line").eq(0).focusSelector({
            'selectedElement':$('> ul > li.selected', this.$ctx)
        });

        if (!this.$ctx.hasClass("skin-main-navigation-microsite")) {
            $('.linkList > li > a', this.$ctx).each(function () {
                var $icon = $('<span class="icon icon-arrow-flyout-navigation">&nbsp;</span>');
                $(this).append($icon);
            });
        }

        //
        // nth-child emulation
        //
        if (!$('html').hasClass('nthchild')) {
            $('.linkList > li > ul', this.$ctx).each(function () {
                Tc.Utils.BrowserUtils.nthChild($('> li', this), 4);
            });

            $('.pictureList', this.$ctx).each(function () {
                Tc.Utils.BrowserUtils.nthChild($('> li', this), 3);
            });

            $('.selectablePictureList', this.$ctx).each(function () {
                Tc.Utils.BrowserUtils.nthChild($('> li', this), 5);
            });
        }


        /**
         * Hook function to bind the module specific events.
         *
         * Binds the following events:
         *
         *    - onClick on all ul li > a elements
         *    - initializes selectmenu plugin on selectboxes
         *
         * @protected
         * @method on
         * @return {void}
         */
        this.on = function (callback) {
            // calling parent method
            parent.on(callback);

            var that = this;

            //
            // If ".skin-main-navigation-microsite" class is available, we don't need the event handlers. So we ignore it.
            //
            var eventHandler = $.proxy(function (evt) {
                this.toggle(evt.target);

                evt.preventDefault();
                return false;
            }, that);

            //
            // Adding click / hover handler to all Elements within Documentation
            //
            $('ul li > a', this.$ctx).each(function () {
                var $this = $(this);
                var $parent = $this.parents('ul').eq(0);

                //
                // ignore selectabledPictureList
                //

                if (!$parent.hasClass('selectablePictureList') && !$parent.hasClass('formSet')) {
                    if (!$this.parents('ul').eq(0).hasClass('linkList')) {
                        $this.click(eventHandler);
                    } else {
                        $this.mouseenter($.proxy(function (evt) {
                            this.open(evt.target);

                            evt.preventDefault();
                            return false;
                        }, that));
                    }
                }
            });


            //
            // Bind specific configuration to selectmenu in selectablePictureList (3rd level navigation)
            //
            $('.selectablePictureList li select', this.$ctx).each(function () {
                var maxCharLen = Tc.Utils.Form.getMaxCharLength(this);

                $(this).selectmenu({
                    'menuWidth'      :138 + ( maxCharLen > 25 ? 11 : 0),
                    'positionOptions':{
                        'offset':( maxCharLen > 25 ? '-6 0' : null)
                    },

                    'open'  :function (evt, ui) {
                        that.open(evt.target);
                    },
                    'close' :function (evt, ui) {
                        that.close(evt.target, {'animate':false});
                    },
                    'change':function (evt, ui) {
                        if (ui && ui.value !== '') {
                            window.location.href = ui.value;
                        }
                    }
                });
            });
        };


        /**
         * Returns the nav-level for the navigation element.
         *
         * @private
         * @method getNavigationLevel
         * @param {jQuery} element A element to retrieve the navigation level for.
         * @return {int}
         */
        this.getNavigationLevel = function (element) {
            return $(element).parents("ul").size();
        };

        /**
         * Returns the given NavigationElement on top of the clicked anchor.
         *
         * @private
         * @method getNavigationElement
         * @param {jQuery} el A element to get the navigation element for.
         * @return {jQuery}
         */
        this.getNavigationElement = function (el) {
            return $(el).closest("li");
        };

        /**
         * Toggles the MainNavigation status.
         *
         * @public
         * @method toggle
         * @param {jQuery} el Used to define which menupoint should be toggled
         * @return {void}
         */
        this.toggle = function (el) {
            var $target = this.getNavigationElement(el);

            if (!$target.hasClass("open")) {
                this.open(el);
            } else {
                this.close(el);
            }
        };

        /**
         * Opens the Mainnavigation.
         * Additional options are available using the options object.
         *
         * Options:
         *
         * - animate : (boolean) Animate fadeIn/fadeOut
         *
         * @public
         * @method open
         * @param {jQuery} navId Defines the menuoption to open.
         * @param {object} options Additional settings as object.
         * @return {void}
         */
        this.open = function (el, options) {
            options = $.extend({
                'animate':false
            }, options);

            var $target = this.getNavigationElement(el);
            var wasActiveBeforeOpen = $target.hasClass('open');

            var liHeight;
            if (this.$ctx.hasClass("skin-main-navigation-microsite")) liHeight = 131; else liHeight = 369;

            $target.find("li").removeClass("open");
            //
            // no animation when the current element is already activated
            //
            if (!wasActiveBeforeOpen) {
                if (this.getNavigationLevel($target) > 1) {
//                    if(window.console) console.log("1");
                    $target.find("> ul").css("opacity", 0).stop().animate({
                        "opacity":1
                    });
                } else {
//                    if(window.console) console.log("2");
                    $target.find("> ul > li, > div.formSet > *").css("opacity", 0).stop().animate({
                        "opacity":1
                    });
                }
            }
            //
            // add style classes for visual update
            //
            $target.parent().find("> li").each(function () {
                var $this = $(this);
                if ($this.attr("id") === $target.attr("id")) {
                    $this.addClass("open");
                } else {
                    $this.removeClass("open");
                }
            });

            //
            // check if clicked navigationitem is a first level element.
            // updates focusselector
            //
            if (this.getNavigationLevel($target) === 1) {
                $target.stop().animate({
                    "height":liHeight
                }, null, null, function () {
                    $target.parent().find("> li:not(#" + $target.attr("id") + ")").css("height", "auto");
                });

                this.$ctx.parents(".line").focusSelector('setPosition', el).focusSelector('setState', 'open');
            }


            //
            // no animation when the current element is already activated
            //
            if (!wasActiveBeforeOpen) {
                if (options.animate === true) {
//                    if(window.console) console.log("3");
                    $target.find("ul").stop().animate({
                        "opacity":1
                    });
                } else {
//                    if(window.console) console.log("4");
                    if (this.$ctx.hasClass("skin-main-navigation-microsite"))
//                        $target.find("ul").show().css('opacity', 1);
                        $target.find("ul").css('opacity', 1).slideDown(400);
                    else
                        $target.find("ul").css('opacity', 1);
                }
            }

        };

        /**
         * Closes the currently opened Menu.
         * For options details see open.
         *
         * @public
         * @method close
         * @param {jQuery} navId Defines the menuoption to close.
         * @param {object} options Additional settings as object.
         * @return {void}
         */
        this.close = function (el, options) {
            options = $.extend({
                'animate':true
            }, options);

            var $target = this.getNavigationElement(el);

            //
            // check if clicked navigationitem is a first level element.
            // updates focusselector
            //
            if (this.getNavigationLevel($target) === 1) {
                $target.animate({
                    "height":25
                });

                var $focusSelector = this.$ctx.parents(".line").eq(0);

                $focusSelector.focusSelector('resetPosition');
                $focusSelector.focusSelector('setState', 'close');
            }

            //
            //
            //
            var callback = (function () {
                $target.removeClass("open");
            });

            if (options.animate === true) {
                $target.find("> ul").animate({
                    "opacity":0
                }, null, null, callback);
            } else {
                callback();
            }

        };


        /**
         *
         * @method after
         */
        this.after = function () {
            // calling parent method
            parent.after();
        };
    };
})(Tc.$);
