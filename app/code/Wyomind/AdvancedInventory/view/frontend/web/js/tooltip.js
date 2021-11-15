require(['jquery', 'jquery/ui'], function($){
    jQuery(function($) {
        var $tooltip = $('<div class="lite-tooltip" id="tooltip"></div>'),
            selector = '[data-lite-tooltip]',
            tip_offset = 2,
            tip_size = 6 + tip_offset,
            positions = {
                top: function(o){
                    return {
                        top: (o.bbox.top + o.st) - o.ttHeight - tip_size,
                        left: o.bbox.left + (o.bbox.width / 2) - (o.ttWidth / 2)
                    };
                },
                bottom: function(o){
                    return {
                        top: (o.bbox.bottom + o.st) + tip_size,
                        left: o.bbox.left + (o.bbox.width / 2) - (o.ttWidth / 2)
                    };
                },
                right: function (o) {
                    return {
                        top: (o.bbox.bottom + o.st) - (o.ttHeight / 2) - (o.bbox.height / 2),
                        left: o.bbox.right + tip_size
                    };
                },
                left: function (o) {
                    return {
                        top: (o.bbox.bottom + o.st) - (o.ttHeight / 2) - (o.bbox.height / 2),
                        left: o.bbox.left - o.ttWidth - tip_size
                    };
                }
            };

        $(document.body).append($tooltip);

        $(document)
            .on('mouseenter click', selector, function(e) {
                var el = $(this),
                    data = el.data(),
                    content = data.liteTooltip || 'Tooltip\'s Placeholder',
                    position = data.liteTooltipPosition || 'top',
                    tooltipWidth = data.liteTooltipWidth * 1 || 280,
                    bbox = e.currentTarget.getBoundingClientRect(),
                    tooltipHeight;

                // sanity check
                if (!positions[position]) position = 'top';
                // inject content into tooltip
                $tooltip
                    .html('')
                    .css({ width: tooltipWidth })
                    .html(content);

                // grab height after injecting
                tooltipHeight = $tooltip.outerHeight();
                // calculate tooltip's position
                var ttOffset = positions[position]({
                    ttWidth: tooltipWidth,
                    ttHeight: Math.ceil(tooltipHeight),
                    bbox: bbox,
                    st: document.documentElement.scrollTop || document.body.scrollTop
                });
                // position and show tooltip
                setTimeout(function() {
                    if ( $(selector+':hover').length ) {
                        $tooltip
                            .css({ top: ttOffset.top, left: ttOffset.left, opacity: 1 })
                            .attr('class', 'lite-tooltip ' + (position == 'top' ? '' : 'lite-tooltip-'+position) );
                    }
                }, 150);
            })
            .on('mouseleave', selector, function(e) {
                setTimeout(function() {
                    if ( !$tooltip.is(':hover') ) {
                        $tooltip.css({ opacity: 0, top: '-100%', left: '-100%' });
                    }
                }, 100);
            });

        $tooltip.on('mouseleave', function(e) {
            setTimeout(function() {
                if (!$(selector+':hover').length) {
                    $tooltip.css({ opacity: 0, top: '-100%', left: '-100%' });
                }
            }, 100);
        });
    });
});