// js for mfrb_template


/*
 * mfrb_template initialisation
 */
elgg.provide('elgg.mfrb_template');
elgg.provide('mfrb');


/*
 * Init. Loaded for the first time only
 */
elgg.mfrb_template.init = function() {

	$(document).ready(function() {
		elgg.river.autoload_river();
	});

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

	// toggle dropzone
	$('body').on('click', '.toggle-dropzone', function() {
		var $this = $(this);
		$this.css('cursor', 'progress');
		require(['elgg_dropzone'], function(dz) {
			dz.init();
			$this.css('cursor', 'auto');
			$this.closest('.elgg-form').find('.filesbox').slideToggle('medium');
		}, function (err) {
			$this.css('cursor', 'auto');
		});
	});

	// bind resize window
	$(window).bind('resize.mfrb_template', function() {
		if (!$('.elgg-page-admin').length) elgg.mfrb_template.resize();
	});
	if (!$('.elgg-page-admin').length) elgg.mfrb_template.resize();

	// goTop button
	var gT = $('#goTop'),
		lastScrollY = 0;

	$(window).on('scroll.window', function() {
		var shadowHeight = Math.log(window.scrollY/100);

		// goTop
		(window.scrollY > 150) ? gT.addClass('scrolled') : gT.removeClass('scrolled');

		// topbar shadow
		$('div.elgg-page-topbar').css('box-shadow', '0 0 ' + (shadowHeight/4+3) + 'px ' + shadowHeight + 'px rgba(0,0,0,0.1)');

		// Scroll fixed elements
		elgg.mfrb_template.followScroll(lastScrollY);

		lastScrollY = window.scrollY;

	});
	elgg.mfrb_template.followScroll();

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
elgg.register_hook_handler('init', 'system', elgg.mfrb_template.init);



/*
 * Resize, scroll and fix sidebar
 */
elgg.mfrb_template.resize = function() {
	var windowWidth = $(window).width();

	$.each($('.elgg-layout'), function() {
		var $this = $(this),
			$es = $this.find('.elgg-sidebar'),
			$esa = $this.find('.elgg-sidebar-alt'),
			$body = $this.children('.elgg-body'),
			moved = $this.find('.elgg-sidebar-alt .elgg-sidebar').length;

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
	});

	elgg.mfrb_template.followScroll();
	$('#toggle-sidebar').removeClass('fi-arrow-left').addClass('fi-arrow-right');
};


/**
 * Contraints fixed elements to scroll and stay inside the border of the viewport
 * @param  {[type]} lastScrollY   last window.scrollY. Helper to know direction of the scroll
 * @return {[type]}               [description]
 */
elgg.mfrb_template.followScroll = function(lastScrollY) {
	var wH = $(window).height(),
		lastScrollY = lastScrollY || 0;

	$.each($('.elgg-layout:not(.hidden)').find('div[follow-scroll], div.elgg-sidebar, div.elgg-sidebar-alt'), function() {//, div.elgg-sidebar-alt'), function() {
		var windowY = window.scrollY,
			scrollOffset = lastScrollY - windowY,
			$this = $(this).removeClass('hidden'), // hidden to prevent ugly effect on first load
			$elem = eval($this.attr('pushedBy')),
			elemH = $elem ? $elem.outerHeight() : 0,
			maxTop = 50, //$('.elgg-page-topbar').height(),
			maxBottom = -($this.outerHeight()+maxTop - wH),
			thisBottom = $this.css('bottom') == 'auto' ? maxBottom : $this.css('bottom'),
			px = parseFloat(thisBottom) - parseFloat(scrollOffset);

		if (maxBottom - elemH < 0) {
			if (scrollOffset > 0) { // le contenu descend, on monte dans la page
				if (windowY < elemH) maxBottom -= elemH - Math.max(0, windowY);
				if (this.offsetTop >= maxTop || px < maxBottom) px = maxBottom;
			} else {
				if (px > 0) px = Math.max(0, maxBottom);
				if (this.offsetTop >= maxTop && windowY <= 0) px = maxBottom - elemH;
			}
			$this.css({bottom: px +'px'});
		} else if ($elem) {
			var thisTop = wH - Math.min($elem[0].getBoundingClientRect().top, maxTop) + elemH + $this.outerHeight();

			if (scrollOffset > 0) { // le contenu descend, on monte dans la page
				if (windowY < elemH) {
					px = thisTop;
				} else {
					px = maxBottom;
				}
			} else {
				px = Math.min(thisTop, maxBottom);
			}
			$this.css({bottom: px +'px'});
		}
	});
};



elgg.mfrb_template.reload_js = function() {
	// resize sidebars
	elgg.mfrb_template.resize();
	// load river
	elgg.river.autoload_river();


	// Send to Piwik tracker
	if (typeof piwikTracker != 'undefined' && typeof piwikTracker.trackPageView == 'function') {
		piwikTracker.setDocumentTitle(document.title);
		piwikTracker.setCustomUrl(window.location.href);
		piwikTracker.trackPageView();
	}
};
elgg.register_hook_handler('history', 'reload_js', elgg.mfrb_template.reload_js);


elgg.history.register_storable_page('adherents/map', {
	callbackOnStore: function() {
		$('.elgg-page-topbar').removeClass('shadow');
	},
	callbackOnRestore: function(elem) {
		// change state-selected in elgg-page-topbar
		$('.elgg-page-topbar .elgg-state-selected').removeClass('elgg-state-selected');
		$('.elgg-page-topbar .elgg-menu-item-map-adherent').addClass('elgg-state-selected');

		// remove some stuff
		$('.elgg-menu-hover, .tipsy, .elgg-popup:not(.pinned)').remove();
		elem.fadeIn().removeClass('hidden');
		$('.elgg-page-topbar').addClass('shadow');
	}
});


elgg.history.register_storable_page('adherents/list', {
	callbackOnRestore: function(elem) {
		// change state-selected in elgg-page-topbar
		$('.elgg-page-topbar .elgg-state-selected').removeClass('elgg-state-selected');
		$('.elgg-page-topbar .elgg-menu-item-adherents').addClass('elgg-state-selected');

		// remove some stuff
		$('.elgg-menu-hover, .tipsy, .elgg-popup:not(.pinned)').remove();
		elem.fadeIn().removeClass('hidden');
	}
});


elgg.history.register_storable_page('adherents/statistics', {
	callbackOnRestore: function(elem) {
		// change state-selected in elgg-page-topbar
		$('.elgg-page-topbar .elgg-state-selected').removeClass('elgg-state-selected');
		$('.elgg-page-topbar .elgg-menu-item-adherents').addClass('elgg-state-selected');

		// remove some stuff
		$('.elgg-menu-hover, .tipsy, .elgg-popup:not(.pinned)').remove();
		elem.fadeIn().removeClass('hidden');
	}
});



/**
 * Fill title input with name of the file when file is uploaded with dropzone.
 */
mfrb.dropzoneUpload = function(name, type, params, value) {
	if (params.data && params.data.output) {
		var $twe = $(params.file.previewElement).closest('.thewire-extra');
		if ($twe.length) {
			// hide elgg-dropzone instructions if maxFiles is reached
			if (params.dropzone.files.length >= params.dropzone.options.maxFiles) {
				$twe.find('.elgg-dropzone-instructions').animate({height: 0, padding: 0, opacity: 0}, function() {
					$(this).hide();
					$twe.find('.elgg-input-dropzone .elgg-dropzone-preview').first().css('border', 0);
				});
			}
		}
	}
};
elgg.register_hook_handler('upload:success', 'dropzone', mfrb.dropzoneUpload);



