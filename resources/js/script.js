        (function() {

            function SVGMenu(el, options) {
                this.el = el;
                this.init();
            }

            SVGMenu.prototype.init = function() {
                this.trigger = this.el.querySelector('button.menu__handle');
                this.shapeEl = this.el.querySelector('div.morph-shape');

                var s = Snap(this.shapeEl.querySelector('svg'));
                this.pathEl = s.select('path');
                this.paths = {
                    reset: this.pathEl.attr('d'),
                    open: this.shapeEl.getAttribute('data-morph-open'),
                    close: this.shapeEl.getAttribute('data-morph-close')
                };

                this.isOpen = false;

                this.initEvents();
            };

            SVGMenu.prototype.initEvents = function() {
                this.trigger.addEventListener('click', this.toggle.bind(this));
            };

            SVGMenu.prototype.toggle = function() {
                var self = this;

                if (this.isOpen) {
                    classie.remove(self.el, 'menu--anim');
                    setTimeout(function() {
                        classie.remove(self.el, 'menu--open');
                    }, 250);
                } else {
                    classie.add(self.el, 'menu--anim');
                    setTimeout(function() {
                        classie.add(self.el, 'menu--open');
                    }, 250);
                }
                this.pathEl.stop().animate({
                    'path': this.isOpen ? this.paths.close : this.paths.open
                }, 350, mina.easeout, function() {
                    self.pathEl.stop().animate({
                        'path': self.paths.reset
                    }, 800, mina.elastic);
                });

                this.isOpen = !this.isOpen;
            };

            new SVGMenu(document.getElementById('menu'));

        })();

        // Plugin options and our code
        $("#modal_trigger").leanModal({
            top: 100,
            overlay: 0.6,
            closeButton: ".modal_close"
        });

        $(function() {
            // Calling Login Form
            $("#login_form").click(function() {
                $(".social_login").hide();
                $(".user_login").show();
                return false;
            });

            // Calling Register Form
            $("#register_form").click(function() {
                $(".social_login").hide();
                $(".user_register").show();
                $(".header_title").text('Register');
                return false;
            });

            // Going back to Social Forms
            $(".back_btn").click(function() {
                $(".user_login").hide();
                $(".user_register").hide();
                $(".social_login").show();
                $(".header_title").text('Login');
                return false;
            });
        });

        // hide password input, forgot password and button submit
        $('#password').hide();
        $('#btn-submit').hide();
        $('#forgot-password').hide();

        $('#email').keyup(function() {
            // if email input is not empty fadeIn password input
            if ($(this).val() !== '') {
                $('#password').fadeIn(500);
                $('#forgot-password').fadeIn(500);
            } else if ($('#btn-submit').is(":visible")) {
                $('#btn-submit').fadeOut(500);
                $('#password').delay(500).fadeOut(500);
                $('#forgot-password').delay(500).fadeOut(500);
            } else {
                $('#password').fadeOut(500);
                $('#forgot-password').fadeOut(500);
            }
        });

        $('#password').keyup(function() {
            // if password input is not empty fadeIn submit button
            if ($(this).val() !== '') {
                $('#btn-submit').fadeIn(500);
            } else {
                $('#btn-submit').fadeOut(500);
            }
        });

        $(document).ready(function() {
            $('.animsition').animsition();
        });
