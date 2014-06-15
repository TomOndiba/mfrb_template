
/**
 * Wrapper for History.replaceState
 */
elgg.history.replaceState = function(data, title, url) {
	require(['history'], function() {
		History.replaceState(data, title, url);
	});
};



/**
 * Wrapper for History.pushState
 */
elgg.history.pushState = function(data, title, url) {
	require(['history'], function() {
		History.pushState(data, title, url);
	});
};



/**
 * Programmatically change url in browser without load the page. Only url is changed.
 * To do that, we need to register a hook on history statechane to return true, and remove it just after.
 *
 * @param {String}    url       Url to put in browser url (optional). If no url is provided, elgg.history.data.referer is the url.
 * @param {boolean}   push      Set to true to push in history state.
 */
elgg.history.changeUrl = function(url, push) {
	var url = url || elgg.history.data.referer,
		State = push ? 'pushState' : 'replaceState';

	elgg.register_hook_handler('history', 'statechange', elgg.history.interceptHistory);
	elgg.history[State]({randomData: Math.random()}, null, url);
};
// the hook that return false and remove himself
elgg.history.interceptHistory = function() {
	elgg.unregister_hook_handler('history', 'statechange', elgg.history.interceptHistory);
	return false;
};



/**
 * Unregister a hook handler
 * @param {String}   name     Name of the plugin hook
 * @param {String}   type     Type of the event
 * @param {Function} handler  Handle to remove
 * @return {bool}
 */
elgg.unregister_hook_handler = function(name, type, handler) {
	var priorities =  elgg.config.hooks;

	if (priorities[name][type] instanceof elgg.ElggPriorityList) {
		priorities[name][type].remove(handler);
	}
};



/**
 * Progress bar
 */
elgg.history.progressBarInterval = null;
elgg.history.progressBar = function(action) {
	var $b = $('body'),
		$p = $('#progress'),
		aL = 'ajaxLoading';

	if (action == 'start' && !elgg.history.progressBarInterval) {
		$b.addClass(aL);
		$p.css({width: 0});
		elgg.history.progressBarInterval = setInterval(function() {
			var windowWidth = $(window).width(),
				width = Math.min($p.width() + Math.floor(Math.random() * (windowWidth*0.2) + 50), windowWidth*0.9);
			$p.animate({width: width}, 250);
		}, 300);
	} else if (action == 'stop') {
		clearInterval(elgg.history.progressBarInterval);
		elgg.history.progressBarInterval = null;
		elgg.history.data = {
			$this: null,
			url: null,
			referer: elgg.normalize_url(decodeURIComponent(window.location.href)), // We set referer for History changestate
			dataForm: [],
			forward: false
		};
		$p.animate({width: '100%'}, 250);
		$b.removeClass(aL);
	}
};



/**
* Helper to register some actions who perform event and stop click at history click hook.
* @param  {string}      match       Check if url match this string.
* @param  {object}      options     Object passed to options of elgg.action. Parameters passed throw are (params, json, ...).
*                                   Typically: {
*                                                  success: function(params, json) {
*                                                       // code to execute when ajax return success
*                                                  },
*                                                  error: function() {}
*                                              }
* @param  {integer}     priority    Priority of the hook handler.
* @return {bool}                    Return false if action is executed or original value.
*/
elgg.history.register_direct_action = function(match, options, priority) {
	var executeDirectAction = function executeDirectAction(name, type, params, value) {
		if (value && elgg.history.data.url.match(elgg.get_site_url() + 'action/' + match)) {
			// Always clear elgg.history.$this variable and stop progress bar on complete.
			var custom_success = options.success || elgg.nullFunction,
				custom_complete = options.complete || elgg.nullFunction;

			options.success = function(json) {
				custom_success(json);
			};
			options.complete = function(json) {
				custom_complete(json);
				elgg.history.progressBar('stop');
			};

			if (elgg.isUndefined(options.progressBar) || options.progressBar !== false) elgg.history.progressBar('start');
			elgg.action(elgg.history.data.url, options);
			return false;
		} else {
			return value;
		}
	};

	elgg.register_hook_handler('history', 'click', executeDirectAction, priority);
};



/**
 * Store a page in the dom. Set hidden class to elgg-layout. Add id to this elgg-layout
 * @param  {string} url    url of the origin page
 */
elgg.history.store_page = function(url) {
	if (!url) return false;
	var urlToID = function(match) {
			if (!match) return false;
			return match[0].replace(/[^a-z0-9]/gi, '');
		};

	$.each(elgg.history.registered_stored_page, function(key, callbacks) {
		var matchedPage = url.match(elgg.get_site_url() + key);

		if (matchedPage) {
			//console.log($('title').html(), 'title');
			callbacks.callbackOnStore();
			$('.elgg-layout:not(.hidden)')
				.addClass('hidden')
				.attr('id', 'stored_page_' + (callbacks.matchOnUrlOrKey == 'url' ? urlToID(matchedPage) : key.replace(/[^a-z0-9]/gi, '')) )
				.css('display', '')
				.data('page_title', $('title').html());
			return false; // break loop
		}
	});
};



/**
 * Restore a page from the dom previously stored by elgg.history.store_page
 * @param are givens by hook
 * @return {bool}        return value given by hook or false if page are storable and stored in dom.
 */
elgg.history.restore_page = function(name, type, params, value) {
	if (!value) return false;

	var urlToID = function(match) {
			if (!match) return false;
			return match[0].replace(/[^a-z0-9]/gi, '');
		},
		matched = false;

	$.each(elgg.history.registered_stored_page, function(key, callbacks) {
		var matchedPage = elgg.history.data.url.match(elgg.get_site_url() + key),
			$storedPage = $('#stored_page_' + (callbacks.matchOnUrlOrKey == 'url' ? urlToID(matchedPage) : key.replace(/[^a-z0-9]/gi, '')) );

		if (matchedPage && $storedPage.length) {
			// if forward, we replace the page
			if (elgg.history.data.forward) {
				$storedPage.remove();
				return value;
			} else {
				matched = true;
				elgg.history.store_page(elgg.history.data.referer); // check if referer also could be stored.
				$('.elgg-layout:not(.hidden)').remove(); // remove non-hidden elgg-layout.
				$('title').html($storedPage.data('page_title'));
				$('div[follow-scroll], div.elgg-layout > div.elgg-sidebar, div.elgg-sidebar-alt').css('bottom', 'auto');
				callbacks.callbackOnRestore($storedPage);
				elgg.history.progressBar('stop');

				return false;
			}
		}
	});

	if (matched) return false;
	return value;
};
elgg.register_hook_handler('history', 'get_page', elgg.history.restore_page);



/**
 * Register a page which can be stored in the dom.
 * @param  {string}    regex       a regex that match url
 * @param  {object}    callbacks   function will be executed on store and on restore. When called, it's return correspondant $('.elgg-layout') in param.
 */
elgg.history.registered_stored_page = {};
elgg.history.register_storable_page = function(regex, callbacks) {
	var _default = {
			matchOnUrlOrKey: 'url', // by default match on 'url', else it could match by key
			callbackOnStore: $.noop,
			callbackOnRestore: $.noop
		};

	elgg.history.registered_stored_page[regex] = $.extend(_default, callbacks);
};

// register activity page
elgg.history.register_storable_page('activity(.*)', {
	matchOnUrlOrKey: 'key',
	callbackOnStore: function() {
		$('.elgg-layout:not(.hidden)').data('page_owner', elgg.page_owner);
	},callbackOnRestore: function(elem) {
		// change state-selected in elgg-page-topbar
		$('.elgg-page-topbar .elgg-state-selected').removeClass('elgg-state-selected');
		$('.elgg-page-topbar .elgg-menu-item-activity').addClass('elgg-state-selected');

		// remove some stuff
		$('.elgg-menu-hover, .tipsy, .elgg-popup:not(.pinned)').remove();
		elem.fadeIn().removeClass('hidden');
		elgg.page_owner = elem.data('page_owner');
		elgg.history.restore_tabs();
	}
});

// register group profile page
elgg.history.register_storable_page('groups/profile/(.*)', {
	callbackOnStore: function() {
		$('.elgg-layout:not(.hidden)').data('page_owner', elgg.page_owner);
	},
	callbackOnRestore: function(elem) {
		// change state-selected in elgg-page-topbar
		$('.elgg-page-topbar .elgg-state-selected').removeClass('elgg-state-selected');

		// remove some stuff
		$('.elgg-menu-hover, .tipsy, .elgg-popup:not(.pinned)').remove();
		elem.fadeIn().removeClass('hidden');
		elgg.page_owner = elem.data('page_owner');
		elgg.mfrb_template.resize();
	}
});



/**
 * Store tabs in the dom
 */
elgg.history.store_tabs = function store_tabs(name, type, params, value) {
	if (value && elgg.history.data.$this) {
		var $tabs = elgg.history.data.$this.closest('.elgg-tabs, .elgg-menu-filter-default');

		if ($tabs.length) {
			// Set ID to selected tab before change
			var $activeA = $tabs.find('li.elgg-state-selected a'),
				$activeTab = $tabs.nextAll('ul:visible');

			$activeTab.addClass('storable-tab hidden').css('display', '');
			if (!$activeTab.attr('id')) $activeTab.attr('id', $activeA.attr('href').replace(/[^a-z0-9]/gi, '')).data('page_title', params.title);

			// Show tab clicked
			var $respBody = $(params.body),
				$respA = $respBody.find('a[href="'+ elgg.history.data.url +'"]'), // || $respBody.find('a[href="'+ elgg.history.data.url +'*"]').first(),
				$respTabs = $respA.closest('.elgg-tabs, .elgg-menu-filter-default'),
				$respUL = $respTabs.next('ul').addClass('storable-tab').attr('id', $respA.attr('href').replace(/[^a-z0-9]/gi, ''));

			$tabs.after($respUL.fadeIn()).replaceWith($respTabs);

			$('title').html(params.title);
			$('.elgg-menu-hover, .tipsy, .elgg-popup:not(.pinned)').remove();

			return false;
		}

	}
	return value;
};
elgg.register_hook_handler('history', 'display_page', elgg.history.store_tabs, 490);



/**
 * Restore a tab previously stored in the dom
 */
elgg.history.restore_tabs = function restore_tabs(name, type, params, value) {
	var $tab = $('.elgg-layout:not(.hidden) #'+elgg.history.data.url.replace(/[^a-z0-9]/gi, ''));

	if (!$tab.length) {
		return value;
	} else if (elgg.history.data.url == elgg.history.data.referer) { // prevent action than forward_url to the same page for refresh page
		return true;
	}

	var $a = $('a[href="'+ elgg.history.data.url +'"]');

	$tab.parent().find('ul.storable-tab').addClass('hidden').css('display', '');
	$tab.fadeIn().removeClass('hidden');
	$a.closest('.elgg-tabs, .elgg-menu-filter-default').find('li').removeClass('elgg-state-selected');
	$a.parent('li').addClass('elgg-state-selected');
	$('title').html($tab.data('page_title'));

	return false;

};
elgg.register_hook_handler('history', 'get_page', elgg.history.restore_tabs, 490);

$.fn.serializeObject = function()
{
	var o = {};
	var a = this.serializeArray();
	$.each(a, function() {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};

