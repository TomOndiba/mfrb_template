$('.scrollable').bind('mousewheel DOMMouseScroll', function (evt) {
	var e0 = evt.originalEvent,
		delta = e0.wheelDelta || -e0.detail;

	this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
	evt.preventDefault();
});


/**
 * Turn an element scrolable and prevent page scrolling when scrolling content
 * http://stackoverflow.com/questions/7600454/how-to-prevent-page-scrolling-when-scrolling-a-div-element
 * @return {jQueryObject}    can be chainable
 */
$(document).on('mousewheel DOMMouseScroll', '.scrollable', function(e) {
	$(this).bind('mousewheel DOMMouseScroll', function (e) {
		var delta = e.wheelDelta || (e.originalEvent && e.originalEvent.wheelDelta) || -e.detail,
			bottomOverflow = this.scrollTop + $(this).outerHeight() - this.scrollHeight >= 0,
			topOverflow = this.scrollTop <= 0;

		if ((delta < 0 && bottomOverflow) || (delta > 0 && topOverflow)) {
			e.preventDefault();
		}
	});
});



$(document).on('keyup', '.thewire-textarea', function() {
	var text = this.value.substring(0, this.selectionEnd),
		mention;

	if (mention = text.match(/\W@(\S*)$/)) {
		console.log(mention[1]);
	}
});



/**
 * Resize automaticaly a textarea who got autoresize attribute
 */
elgg.ui.textarea_autoresize = function() {
	$(document).on({
		focus: function() {
			if (!$(this).data('size')) $(this).data('size', $(this).height());
		},
		keyup: function() {
			var $this = $(this),
				method = $.browser.mozilla ? 'height' : 'innerHeight';

			$this.height($this.data('size'));

			$this[method](this.scrollHeight);
		}
	}, 'textarea[autoresize]');
};
elgg.register_hook_handler('init', 'system', elgg.ui.textarea_autoresize);



/**
 * Update each minute all friendly times
 *
 */
elgg.provide('elgg.friendly_time');

elgg.friendly_time = function(time) {

	//TODO friendly:time hook

	diff = new Date().getTime()/1000 - parseInt(time);

	minute = 60;
	hour = minute * 60;
	day = hour * 24;

	if (diff < minute) {
			return elgg.echo("friendlytime:justnow");
	} else if (diff < hour) {
		diff = Math.round(diff / minute);
		if (diff == 0) {
			diff = 1;
		}

		if (diff > 1) {
			return elgg.echo("friendlytime:minutes", [diff]);
		} else {
			return elgg.echo("friendlytime:minutes:singular", [diff]);
		}
	} else if (diff < day) {
		diff = Math.round(diff / hour);
		if (diff == 0) {
			diff = 1;
		}

		if (diff > 1) {
			return elgg.echo("friendlytime:hours", [diff]);
		} else {
			return elgg.echo("friendlytime:hours:singular", [diff]);
		}
	} else {
		diff = Math.round(diff / day);
		if (diff == 0) {
			diff = 1;
		}

		if (diff > 1) {
			return elgg.echo("friendlytime:days", [diff]);
		} else {
			return elgg.echo("friendlytime:days:singular", [diff]);
		}
	}
}

elgg.friendly_time.update = function() {
	$('.elgg-page .elgg-friendlytime').each(function(){
		var time = $(this).find('time');
		time.html(elgg.friendly_time(time.attr('time')));
	});
}

elgg.friendly_time.init = function() {
	elgg.friendly_time.update();
	setInterval(elgg.friendly_time.update, 1000*60); // each 60 sec
};
elgg.register_hook_handler('init', 'system', elgg.friendly_time.init);



/**
 * Initialize the submenu
 *
 * @param {Object} parent
 * @return void
 */
elgg.ui.initMenu = function(parent) {
	if (!parent) {
		parent = document;
	}

	// submenu menu
	$('body').on('click', '.elgg-menu-submenu', function(e) {
		var $this = $(this),
			$submenu = $this.data('submenu') || null;

		// check if we've attached the menu to this element already
		if (!$submenu) {
			$submenu = $this.next('.elgg-submenu');
			$this.data('submenu', $submenu);
		}

		// close submenu if arrow is clicked & menu already open
		if ($submenu.css('display') == 'block') {
			$submenu.fadeOut();
		} else {
			// @todo Use jQuery-ui position library instead -- much simpler
			var offset = $this.offset();
			var top = $this.height() + offset.top + 'px';
			var left = $this.width() + offset.left - $submenu.width() + 'px';

			$submenu.appendTo('body')
					.css('position', 'absolute')
					.css('top', top)
					.css('left', left)
					.fadeIn('normal');
		}

		// hide any other open hover menus
		$('.elgg-menu-hover:visible').not($submenu).fadeOut();
		return false;
	});

	// hide submenu when user clicks elsewhere
	$('body').on('click', function(event) {
		if ($(event.target).parents('.elgg-submenu').length === 0) {
			$('.elgg-submenu').fadeOut();
		}
	});
};
elgg.register_hook_handler('init', 'system', elgg.ui.initMenu);