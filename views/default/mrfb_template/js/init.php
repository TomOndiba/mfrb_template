// js for mrfb_template


/*
 * mrfb_template initialisation
 */
elgg.provide('elgg.mrfb_template');
elgg.provide('mrfb');



/*
 * Init. Loaded for the first time only
 */
elgg.mrfb_template.init = function() {

	$('#toggle-sidebar').click(function() {
		if ($(this).hasClass('fi-arrow-right')) {
			var mainMargin = '-320px',
				sbMargin = 0,
				mainSpeed = 300,
				sbSpeed = 500;
		} else {
			var mainMargin = 0,
				sbMargin = '-320px',
				mainSpeed = 500,
				sbSpeed = 300;
		}

		$('.elgg-layout-two-sidebar .elgg-main').animate({marginRight: mainMargin}, mainSpeed);
		$('.elgg-sidebar-alt').animate({marginLeft: sbMargin}, sbSpeed);

		$(this).toggleClass('fi-arrow-right fi-arrow-left');
	});

	// bind resize window
	$(window).bind('resize.mrfb_template', function() {
		if (!$('.elgg-page-admin').length) elgg.mrfb_template.resize();
	});
	if (!$('.elgg-page-admin').length) elgg.mrfb_template.resize();

	// goTop button
	var gT = $('#goTop');
	$(window).bind('scroll.window', function() {
		(this.scrollY > 150) ? gT.addClass('scrolled') : gT.removeClass('scrolled');
		var shadowHeight = Math.log(this.scrollY/100);
		$('.elgg-page-topbar').css('box-shadow', '0 0 ' + (shadowHeight/4+3) + 'px ' + shadowHeight + 'px rgba(0,0,0,0.1)');
	});
	gT.click(function() {
		$(window).scrollTo(0, 500);
	});

	var days = [], daysShort = [], daysMin = [], months = [], monthsShort = [];
	for (var i = 0; i <= 6; i++) {
		var day = elgg.echo('date:weekday:'+i);
		days.push(day);
		daysShort.push(day.slice(0, 3));
		daysMin.push(day.slice(0, 2));
	}
	for (var i = 1; i <= 12; i++) {
		var month = elgg.echo('date:month:'+(i < 10 ? "0" + i : i)).replace(' undefined', '');
		months.push(month);
		monthsShort.push(month.slice(0, 3));
	}

	$.datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: '&#x3c;Préc',
		nextText: 'Suiv&#x3e;',
		currentText: 'Courant',
		monthNames: months,
		monthNamesShort: monthsShort,
		dayNames: days,
		dayNamesShort: daysShort,
		dayNamesMin: daysMin,
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	};
	$.datepicker.setDefaults($.datepicker.regional['fr']);
};
elgg.register_hook_handler('init', 'system', elgg.mrfb_template.init);


/*
 * Resize, scroll and fix sidebar
 */
elgg.mrfb_template.resize = function() {
	var windowWidth = $(window).width(),
		$es = $('.elgg-sidebar'),
		$esa = $('.elgg-sidebar-alt'),
		$body = $('.elgg-layout > .elgg-body'),
		moved = $('.elgg-sidebar-alt .elgg-sidebar').length;

	if (windowWidth < 1180 && !moved) {
		$es.appendTo($esa);
	} else if (windowWidth >= 1180 && moved) {
		$es.insertAfter($body);
	}
	if (windowWidth < 940) {
		$esa.css('margin-left', -320);
	} else {
		$esa.css('margin-left', 0);
	}
	$body.css('margin-right', 0);
	$('#toggle-sidebar').removeClass('fi-arrow-left').addClass('fi-arrow-right');
};