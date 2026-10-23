jQuery(document).ready(function($){
    $('.circle-progress').each(function() {
        const $el = $(this);
        const amount = parseFloat($el.attr('amount')) || 0;
        const total = parseFloat($el.attr('total')) || 0;
        const percent = total > 0
            ? Math.min(100, Math.max(0, amount / total * 100))
            : 0;

        const outsideCircle = 70;
        const insideCircle = 60;

        const circumference = 2 * Math.PI * insideCircle;
        const offset = circumference * percent / 100;

        $el.html(`
            <svg viewBox="0 0 ` + outsideCircle * 2 + ` ` + outsideCircle * 2 + `" aria-hidden="true">
                <circle class="track" cx="` + outsideCircle + `" cy="` + outsideCircle + `" r="` + insideCircle + `"></circle>
                <circle class="value" cx="` + outsideCircle + `" cy="` + outsideCircle + `" r="` + insideCircle + `"
                    style="stroke-dasharray: ${offset} ${circumference}">
                </circle>
            </svg>
            <div class="label">${Math.round(percent)}%</div>
        `);

        $el.attr('role', 'progressbar')
            .attr('aria-valuenow', Math.round(percent))
            .attr('aria-valuemin', 0)
            .attr('aria-valuemax', 100);
    });
});