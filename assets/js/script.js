(function ($, window, document) {
    "use strict";

    let WOLFLT = window.WOLFLT || {};

    WOLFLT.DEMO_IMPORTER	= function() {
        var base	= this;

        var progress_bar	= {
            progress_bar_wrapper_element:	'',
            progress_bar_element:			'',
            current_value:					0,
            goto_value:						0,
            timer:							'',
            last_goto_value: 				0,

            show: function show() {
                progress_bar.progress_bar_wrapper_element.addClass('wolf-demo-progress-bar-visible');
            },

            hide: function hide() {
                progress_bar.progress_bar_wrapper_element.removeClass('wolf-demo-progress-bar-visible');
            },

            reset: function reset() {
                clearInterval(progress_bar.timer);

                progress_bar.current_value		= 0;
                progress_bar.goto_value			= 0;
                progress_bar.timer				= '';
                progress_bar.last_goto_value	= 0;

                progress_bar.change(0);
            },


            change: function change(new_progress) {
                progress_bar.progress_bar_element.css('width', new_progress + '%');

                progress_bar.last_goto_value	= new_progress;

                if (new_progress === 100) {
                    clearInterval(progress_bar.timer);
                }
            },

            timer_change: function timer_change(new_progress) {
                clearInterval(progress_bar.timer);

                progress_bar._ui_change(progress_bar.last_goto_value);

                progress_bar.current_value	= progress_bar.last_goto_value;

                clearInterval(progress_bar.timer);

                progress_bar.timer	= setInterval(function () {
                    if (Math.floor((Math.random() * 5) + 1) === 1) {
                        var tmp_value	= Math.floor((Math.random() * 5) + 1) + progress_bar.current_value;

                        if (tmp_value <= new_progress) {
                            progress_bar._ui_change(progress_bar.current_value);

                            progress_bar.current_value	= tmp_value;
                        } else {
                            progress_bar._ui_change(new_progress);
                            clearInterval(progress_bar.timer);
                        }
                    }
                }, 1000);
                progress_bar.last_goto_value = new_progress;
            },

            _ui_change: function change(new_progress) {
                progress_bar.progress_bar_element.css('width', new_progress + '%');
            }
        };

        base.init	= function() {
            $('.wolf-button-install-demo').click(function(e) {
                e.preventDefault();

                var $demo	= $('.wolf-demo');

                if ($demo.hasClass('wolf-demo-installed') || $demo.hasClass('wolf-demo-installing') || $demo.hasClass('wolf-demo-disabled') || $(this).hasClass('button-disabled')) {
                    return;
                }

                var c	= confirm(adiL10n.install_demo_confirm);

                if (c) {
                    base.install($(this).data('demo-id'), {plugins: $(this).data('plugins')});
                }
            });

            $('.wolf-button-uninstall-demo').click(function(e) {
                e.preventDefault();

                var c = confirm(adiL10n.uninstall_demo_confirm);

                if (c) {
                    base.uninstall($(this).data('demo-id'));
                }
            });
        };

        base.install	= function(id, data) {
            var $wrapper	= $('.wolf-demo-' + id);

            $wrapper.addClass('wolf-demo-installing');
            $wrapper.find('.wolf-button-install-demo').addClass('button-disabled');
            $wrapper.find('.wolf-button-install-demo-no-content').addClass('button-disabled');
            $('.wolf-demo').not($wrapper).addClass('wolf-demo-disabled');

            progress_bar.progress_bar_wrapper_element	= $wrapper.find('.wolf-demo-progress-bar-wrapper');
            progress_bar.progress_bar_element			= $wrapper.find('.wolf-demo-progress-bar');
            progress_bar.show();
            progress_bar.change(0);

            base.install_step(id, {wolf_demo_importer_action: 'install', data: data, id: id});
        };

        base.install_finish	= function(id, error) {
            var $wrapper	= $('.wolf-demo-' + id);

            $wrapper.removeClass('wolf-demo-installing');

            if (!error) {
                // finish
                progress_bar.change(100);

                setTimeout(function() {
                    progress_bar.hide();
                    progress_bar.reset();

                    $wrapper.removeClass('wolf-demo-installing').addClass('wolf-demo-installed');
                    $wrapper.find('.wolf-button-install-demo').removeClass('button-disabled');
                    $wrapper.find('.wolf-button-install-demo-no-content').removeClass('button-disabled');
                }, 500);
            } else {
                progress_bar.hide();
                progress_bar.reset();
                $wrapper.find('.wolf-button-install-demo').removeClass('button-disabled');
                $wrapper.find('.wolf-button-install-demo-no-content').removeClass('button-disabled');
            }

            if (confirm(adiL10n.install_finish)) {
                window.location.reload();
            }
        };

        base.install_step	= function(id, data) {
            var $wrapper	= $('.wolf-demo-' + id);

            data	= data || {};

            if (!data.action) {
                data.action		= 'wolf_demo_importer_action';
            }

            if (!data.demo_id) {
                data.demo_id	= id;
            }

            $.ajax({
                type:		'POST',
                url:		ajaxurl,
                cache:		false,
                dataType:	'text',
                data:		data,
                success:	function(content) {
                    if (!content || content == '0') {
                        base.install_finish(id, true);
                        if (confirm(adiL10n.install_demo_error)) {
                            window.location.reload();
                        }
                    } else if (content == '1') {
                        base.install_finish(id);
                    } else {
                        var response	= JSON.parse(content);

                        progress_bar.change(response.progress);

                        var request		= {
                            wolf_demo_importer_action:	response.next_action
                        };

                        if (response.next_action == 'post' && response.pni) {
                            request.pni	= response.pni;
                        }

                        base.install_step(id, request);
                    }
                },
                error:		function() {
                    base.install_finish(id, true);
                    if (confirm(adiL10n.install_demo_error)) {
                        window.location.reload();
                    }
                }
            });
        };

        base.uninstall	= function(id) {
            var $wrapper	= $('.wolf-demo-' + id);

            $wrapper.addClass('wolf-demo-uninstalling').removeClass('wolf-demo-installed');

            progress_bar.progress_bar_wrapper_element	= $wrapper.find('.wolf-demo-progress-bar-wrapper');
            progress_bar.progress_bar_element			= $wrapper.find('.wolf-demo-progress-bar');
            progress_bar.show();
            progress_bar.change(2);
            progress_bar.timer_change(98);

            $.ajax({
                type:		'POST',
                url:		ajaxurl,
                cache:		false,
                dataType:	'text',
                data:		{
                    action:						'wolf_demo_importer_action',
                    wolf_demo_importer_action:	'uninstall',
                    demo_id:					id
                },
                success:	function(content) {
                    progress_bar.change(100);

                    setTimeout(function() {
                        progress_bar.hide();
                        progress_bar.reset();

                        $wrapper.removeClass('wolf-demo-uninstalling');
                        $('.wolf-demo').removeClass('wolf-demo-disabled');
                    })
                },
                error:	function() {
                    $wrapper.removeClass('wolf-demo-uninstalling');
                    alert(adiL10n.uninstall_demo_error);
                }
            });
        };

        base.init();
    };

    $(document).ready(function () {
        WOLFLT.DEMO_IMPORTER();
    });

})(jQuery, window, document);