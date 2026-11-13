/**
 * RC Manager – media library picker for attachment fields.
 * Markup: .rcman-media-picker[data-multiple] containing __input, __select, __clear and __names.
 */
(function ($) {
    'use strict';

    $(function () {
        $('.rcman-media-picker').each(function () {
            var $wrap = $(this);
            var $input = $wrap.find('.rcman-media-picker__input');
            var $names = $wrap.find('.rcman-media-picker__names');
            var multiple = $wrap.data('multiple') === 1 || $wrap.data('multiple') === '1';
            var frame;

            $wrap.on('click', '.rcman-media-picker__select', function (event) {
                event.preventDefault();

                if (!frame) {
                    frame = wp.media({ multiple: multiple ? 'add' : false });
                    frame.on('select', function () {
                        var selection = frame.state().get('selection').toJSON();
                        $input.val(selection.map(function (a) { return a.id; }).join(','));
                        $names.text(selection.map(function (a) { return a.filename; }).join(', '));
                    });
                }

                frame.open();
            });

            $wrap.on('click', '.rcman-media-picker__clear', function (event) {
                event.preventDefault();
                $input.val(multiple ? '' : '0');
                $names.text('');
            });
        });
    });
})(jQuery);
