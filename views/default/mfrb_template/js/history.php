// start of history lib

elgg.provide('elgg.history');

/*
 * Exemple to understand how history js is loaded.
 * You can uncomment to log time and perform benchmark.
 */

/*
benchmarkTimeInit = new Date().getTime();
benchmarkTimeHistory = 0;
console.log(benchmarkTimeInit, 'init'); // Executed when the js loads and the user arrives for the first time on elgg. elgg core could be not loaded.

elgg.provide('elgg.benchmark');
elgg.benchmark.init = function benchmark_init() {
	console.log(new Date().getTime()-benchmarkTimeInit, 'provide'); // Executed after elgg core js is loaded, only on the first time on elgg
	$(document).ready(function() {
		console.log(new Date().getTime()-benchmarkTimeInit, 'ready'); // Executed when DOM is ready, only on the first time on elgg
	});
	$(window).load(function(){ //Executed after everythings (css,js,images) are loaded, only on the first time on elgg
		console.log(new Date().getTime()-benchmarkTimeInit, 'load');
	});

};
elgg.register_hook_handler('init', 'system', elgg.benchmark.init, 0);

elgg.benchmark.click = function benchmark_click() { // Executed when user click on a link
	benchmarkTimeHistory = new Date().getTime();
	console.log(benchmarkTimeHistory, 'click');
};
elgg.register_hook_handler('history', 'click', elgg.benchmark.click, 0);

elgg.benchmark.submit = function benchmark_submit() { // Executed when user submit a form (click or return key)
	benchmarkTimeHistory = new Date().getTime();
	console.log(benchmarkTimeHistory, 'submit');
};
elgg.register_hook_handler('history', 'submit', elgg.benchmark.submit, 0);

elgg.benchmark.statechange = function benchmark_statechange() { // Executed when url change
	console.log(new Date().getTime()-benchmarkTimeHistory, 'statechange');
};
elgg.register_hook_handler('history', 'statechange', elgg.benchmark.statechange, 0);

elgg.benchmark.get_page = function benchmark_get_page() { // Executed before loading page after a click or url change
	console.log(new Date().getTime()-benchmarkTimeHistory, 'get_page');
};
elgg.register_hook_handler('history', 'get_page', elgg.benchmark.get_page, 0);

elgg.benchmark.success = function benchmark_success() { // Executed when page are loaded after a click or url change
	console.log(new Date().getTime()-benchmarkTimeHistory, 'success');
};
elgg.register_hook_handler('history', 'success', elgg.benchmark.success, 0);

elgg.benchmark.display_page = function benchmark_display_page() { // Executed when page are displayed with response data
	console.log(new Date().getTime()-benchmarkTimeHistory, 'display_page');
};
elgg.register_hook_handler('history', 'display_page', elgg.benchmark.display_page, 0);

elgg.benchmark.reload_js = function benchmark_reload_js() { // Executed after page are displayed to reload some javascript
	console.log(new Date().getTime()-benchmarkTimeHistory, 'reload_js');
};
elgg.register_hook_handler('history', 'reload_js', elgg.benchmark.reload_js, 0);

elgg.benchmark.done = function benchmark_done() { // Executed when page are loaded and all stuff are done (reload template and js...)
	console.log(new Date().getTime()-benchmarkTimeHistory, 'done');
};
elgg.register_hook_handler('history', 'done', elgg.benchmark.done, 0);
*/



/**
 * Variable to store destination url, originUrl and clicked element.
 * Plugins can add more parameters.
 * Note: we can't pass jQuery element throw History data.
 * @type {object}
 */
elgg.history.data = {
	$this: null, // clicked jquery element
	url: null, // destination url
	referer: null, // origin url
	dataForm: {},
	forward: false
};



/**
 * Function to initiate full ajax.
 */
elgg.history.init = function() {

	$(window).bind('statechange', function() { //History.Adapter.bind(window, 'statechange', function(event) {
		require(['history'], function() {
			var state = History.getState();

			if (state && elgg.trigger_hook('history', 'statechange', state, true)) {
				elgg.history.get_page({
					url: elgg.normalize_url(decodeURIComponent(window.location.href)),
					forward: state.data.forward ? true : false
				});
			}
		});
	});

	// Internal Helper
	$.expr[':'].internal = function(obj) {
		var url = $(obj).attr('href') || '';
		// Check link
		return url.indexOf(elgg.get_site_url()) === 0 || url.indexOf(':') === -1;
	};

	// prevent scroll with link finished by #
	$('body').on('click', 'a:internal[href$="#"]', function(e){
		return false;
	});

	/**
	 * ajaxify links
	 */
	$('body').on('click',
		// :not is fastest than .not() See http://jsperf.com/jquery-css3-not-vs-not
		'a:internal:not('+
			'[href^="#"],'+
			'[href$="#"],'+
			'[href*="/ajax/"],'+
			'[href*="/logout"],'+
			'[href*="view=rss"],'+
			'[href*="address="],'+
			'[href*="/action/widgets/delete"],'+
			'[href*="/action/friends/"],'+
			'[href*="notifications/personal"],'+
			'[href*="comment/edit"],'+
			'[rel=toggle],'+
			'[rel=popup],'+
			'.noajax'+
		')'
	, function(evt) {
		var $this = $(this),
			href = $this.attr('href');

		// We skip if href is null, undefined or empty. In case of...
		if (elgg.isNullOrUndefined(href) || href === '') {
			evt.preventDefault();
			return;
		}

		// We check if there is confirmation. Continue if not or user accept confirm dialog.
		if ($this.hasClass('elgg-requires-confirmation') && !elgg.ui.requiresConfirmation($this)) return false;

		// Store clicked element
		elgg.history.data = {
			$this: $this,
			url: elgg.normalize_url(decodeURIComponent(href)),
			referer: elgg.normalize_url(decodeURIComponent(window.location.href)),
			dataForm: {}
		};

		// Plugin can hook at this point to perform some action
		// url, refere and $this are passed throw elgg.history.data
		// trigger params contains only click event.
		if (!elgg.trigger_hook('history', 'click', evt, true)) return false;

		// Continue as normal (open link in new tab) for cmd/ctrl+click
		if (evt.which == 2 || evt.metaKey) return true;

		// Parameters for History state data
		// This random data force to bind History statechange either if we invoke same url
		var params = {
			randomData: Math.random()
		};

		// check if this is an action and execute it without change browser url.
		if (elgg.history.data.url.match(elgg.get_site_url() + 'action/')) {
			elgg.history.progressBar('start');
			elgg.action(elgg.history.data.url, {
				success: function(json) {
					console.log('json', json);
					if (json.forward_url) elgg.history.get_page({
						url: json.forward_url,
					});
				},
				complete: function(json) {
					if (!json.responseJSON.forward_url) elgg.history.progressBar('stop');
				}
			});
			return false;
		}

		var parsedUrl = elgg.parse_url(elgg.history.data.url),
			parsedRefererUrl = elgg.parse_url(elgg.history.data.referer);

		if (parsedUrl.fragment && parsedUrl.path == parsedRefererUrl.path) { //same page, go to #hash
			if ($('#'+parsedUrl.fragment).length) $(window).scrollTo($('#'+parsedUrl.fragment), 'slow', {offset:-60});
		} else {
			elgg.history.pushState(params, null, elgg.history.data.url.split("#")[0]);
		}

		return false;
	});

	// Register hook handler for some actions.
	elgg.history.register_direct_action('river/delete', {
		success: function(json) {
			elgg.history.data.$this.closest('.elgg-item').css('background-color', '#FF7777').fadeOut();
		}
	});
	elgg.history.register_direct_action('ccomments/delete', {
		success: function(json) {
			var parsedUrl = elgg.parse_url(elgg.history.data.url);
			$('#item-annotation-'+elgg.parse_str(parsedUrl.query).annotation_id).css('background-color', '#FF7777').fadeOut();
		}
	});


	/**
	 * ajaxify submit forms
	 */
	$('body').on('click',
		'input[type=submit]:not('+
			'#button-signin,'+
			'.noajax'+
		')'
	, function(evt) {

		var $this = $(this),
			$form = $this.closest('form');

		if (!elgg.isUndefined($form.data('validator'))) { // check if form has jquery.validate handler and if it's a valid form
			if (!$form.valid()){
				elgg.register_error(elgg.echo('forms:not_valid'));
				return false;
			}
		}

		// Store clicked element, url of the form, referer and serialized data of the form
		elgg.history.data = {
			$this: $this,
			url: elgg.normalize_url(decodeURIComponent($form.attr('action'))),
			referer: elgg.normalize_url(decodeURIComponent(window.location.href)),
			dataForm: $form.serializeObject() || {}
		};

		// Plugin can hook at this point to perform some action
		// url, referer and $this are passed throw elgg.history.data
		// trigger params contains only click event.
		if (!elgg.trigger_hook('history', 'submit', evt, true)) return false;

		// Continue as normal (open link in new tab) for cmd/ctrl+click
		if (evt.which == 2 || evt.metaKey) return true;

		// Parameters for History state data
		// This random data force to bind History statechange either if we invoke same url
		var params = {
			randomData: Math.random()
		};

		if ($form.hasClass('elgg-form-login')) { // redirect for login
			return true;
		/*} else if ($form.hasClass('elgg-form-editablecomments-edit')) { // Special for editable comment

			elgg.action('editablecomments/edit', {
				data: dataForm,
				success: function(json) {
					var annotation_id = $form.find('input[name=annotation_id]').val();

					$('#editablecomments-edit-annotation-'+annotation_id).toggle();
					replaceHighlight($('#item-annotation-'+annotation_id), json.output);
				}
			});

		} else if ($form.hasClass('elgg-form-comments-add')) { // Special for live comment

			elgg.action('livecomments/add', {
				data: dataForm,
				success: function(json) {
					var orderBy = $form.hasClass('desc') ? 'desc' : 'asc',
						comBlock = $form.closest('.elgg-comments'),
						ul = comBlock.find('ul.elgg-list-annotation'),
						li = $(json.output).find('li:first'),
						txt = li.find('.elgg-output').html(),
						liID = li.attr('id');

					if (orderBy ==  'asc') {
						if (ul.length < 1) {
							comBlock.prepend(json.output);
							if (!$form.hasClass('tiny')) comBlock.prepend($('<h3>', {id: 'comments', 'class': 'gwfb pbs'}).html(elgg.echo('comments')));
						} else {
							ul.append($(json.output).find('li:first'));
						}
					} else if (orderBy == 'desc') {
						if (ul.length < 1) {
							comBlock.prepend($('<h3>', {id: 'comments'}).html(elgg.echo('comments')), json.output);
						} else {
							ul.prepend(li);
						}
					}
					replaceHighlight($('#'+liID), txt);
					$form.find('textarea').val('').height(190);
					$form.find('.preview-markdown').html('').height(178);
					elgg.markdown_wiki.editor();
				}
			});*/

		} else { // ajaxify others forms

			// This is a misuse of elgg.security.addToken() because it is not always a
			// full query string with a ?. As such we need a special check for the tokens.
			if (!elgg.isString(elgg.history.data.dataForm) || elgg.history.data.dataForm.indexOf('__elgg_ts') == -1) {
				elgg.history.data.dataForm = elgg.security.addToken(elgg.history.data.dataForm);
			}

			// check method of the form and do appropriate method
			if ($form.attr('method') == 'post') { // no need to add data to url
				elgg.history.get_page(params);
			} else { // add data to url
				elgg.history.data.url = elgg.history.data.url + '?' + $form.serialize();
				elgg.history.pushState(params, null, elgg.history.data.url);
			}
			//

		}

		return false;
	});

};
elgg.register_hook_handler('init', 'system', elgg.history.init);



/**
* Ajaxified website. Get page called by link or submit form.
* @param  {object}   facultative. Parameters that extend/override elgg.history.data
*/
elgg.history.get_page = function get_page(params) {
	// extend elgg.history.data
	if (!elgg.isUndefined(params)) $.extend(elgg.history.data, params);

	// Plugin can hook at this point to perform some action
	// Parameters are given throw elgg.history.data
	if (!elgg.trigger_hook('history', 'get_page', null, true)) return false;

	elgg.history.progressBar('start');

	// Add some values to formData
	$.extend(elgg.history.data.dataForm, {
		windowWidth: window.innerWidth,
		windowHeight: window.innerHeight,
		navigator: navigator.userAgent,
		touchScreen: !!('ontouchstart' in window)
	});

	// Make ajax call to the url.
	// dataForm is given throw ajax call if this is a form.
	elgg.post(elgg.history.data.url, {
		data: elgg.history.data.dataForm,
		dataType: 'json',
		complete: function(response) {
			if (response.responseJSON.forward_url && response.responseJSON.status >= 0) {

			} else {
				elgg.history.progressBar('stop');
			}
		},
		success: function(response) {
			console.log('succ!!');

			// Plugin can hook at this point to perform some action
			if (!elgg.trigger_hook('history', 'success', response, true)) return false;

			elgg.register_error(response.system_messages.error);
			elgg.system_message(response.system_messages.success);

			// if there is a forward_url and there is no error, we go to forwarded url.
			if (response.forward_url && response.status >= 0) {
				// This is an action !
				// If it's not an action, response doesn't got a forward_url
				// note: when server is down > Object { readyState=0, status=0, statusText="error"}

				//delete params.dataForm;
				//if (params.data) delete params.data.dataForm;
				elgg.history.replaceState({randomData: Math.random(), forward: true}, null, response.forward_url);

			} else if (!response.forward_url) { // So this is a page

				// Trigger all display_page hooks.
				// Each hook have to return value.
				// It can be false to stop other hooks in priority list.
				// It can return 'exit' to stop script here and control scroll page and reload_js.
				if (elgg.trigger_hook('history', 'display_page', response, true) == 'exit') return false;

				eval(response.js_code); // execute javascript from elgg_execute_js. It's include hack to reload js/initialize_elgg forked from elgg/page/reinitialize_elgg


				/*if (urlOffset && params.originURL.path == urlPath) { // same url. Only query offset change, we just slide page body.
					var numOrigin = elgg.isUndefined(params.originURL.query) ? 0 : parseInt(params.originURL.query.match(/offset=(\d+)/)[1]),
						numDest = parseInt(urlOffset[1]);

					$('.elgg-page-body .elgg-layout:visible .elgg-main .elgg-pagination').html($respBody.find('.elgg-main .elgg-pagination').html());
					if (numOrigin < numDest) {
						var u = $('.elgg-page-body .elgg-layout:visible .elgg-main .elgg-list'),
							slideWidth = u.outerWidth(true),
							v = $respBody.find('.elgg-main .elgg-list').clone().css({
								position: 'absolute',
								top: u.position().top,
								left: slideWidth
							});
						u.after(v).add(v).animate({left: '-='+slideWidth+'px'}, function() {
							u.remove();
							v.removeAttr('style');
						});
					} else {
						var u = $('.elgg-page-body .elgg-layout:visible .elgg-main .elgg-list'),
							slideWidth = u.outerWidth(true),
							v = $respBody.find('.elgg-main .elgg-list').clone().css({
								position: 'absolute',
								top: u.position().top,
								right: slideWidth
							});
						u.after(v).add(v).animate({right: '-='+slideWidth+'px'}, function() {
							u.remove();
							v.removeAttr('style');
						});
					}

				} else {*/

				//}

				// Scroll the page.
				// If there is a fragment in the url, scroll to this fragment. Else if noscroll is not to true, set scroll to 0.
				var parsedURL = elgg.parse_url(elgg.history.data.url);
				if (parsedURL.fragment && $('#'+parsedURL.fragment).length) {
					$(window).scrollTo($('#'+parsedURL.fragment), 'slow', {offset:-60});
				} else if (!elgg.history.data.noscroll) {
					$(window).scrollTop(0);
					$('div[follow-scroll], div.elgg-layout > div.elgg-sidebar, div.elgg-sidebar-alt').css('bottom', 'auto');
				}

				// Reload some javascript
				// Plugins can hook some stuffs here
				if (!elgg.trigger_hook('history', 'reload_js', response, true)) return false;

				// Last hook after everythings is done : display page and js reloaded.
				elgg.trigger_hook('history', 'done', response, true);
			}

			//if (params.callback) params.callback(); @todo used for what ???
			//console.log(elgg.history.$this, '$$$$ths');

		},
		error: function(response) {
			console.log(response.status, response);
			//elgg.history.progressBar('stop');
			console.log(response.status != '404', elgg.history.data);
			if (response.status != '404') { // if there is internal server error, we go back.
				elgg.register_hook_handler('history', 'statechange', elgg.history.interceptHistory);
				History.back();
			}
		},
		statusCode: {
			404: function(response) { // we show 404 page
				elgg.trigger_hook('history', 'display_page', response.responseJSON, true);
			},
			500: function() {
				elgg.register_error(elgg.echo('viewfailure', [elgg.history.data.url]));
				console.log('500');
			}
		}

	});
};



/**
 * Display page from response data.
 * @return {object}    Return response data or false to block other hook handler
 */
elgg.history.display_page = function display_page(name, type, params, value) {
	// check if value = true. It allow other plugin to override this code.
	if (value) {
		var $respBody = $(params.body),
			$epb = $('.elgg-page-body > .elgg-inner');

		// Store actual page if it's storable
		elgg.history.store_page(elgg.history.data.referer);

		// Set title of the page
		$('title').html(params.title);

		// remove main elgg-layout
		$epb.children().not('.elgg-layout.hidden').remove();

		// Clean some stuffs
		$('.elgg-menu-hover, .tipsy, .elgg-popup:not(.pinned)').remove();
		$('.elgg-submenu').fadeOut();

		// Display page
		$epb.append($respBody.fadeIn());
		var $topbarSelected = $(params.topbar).find('.elgg-state-selected'),
			$ept = $('.elgg-page-topbar');

		$ept.find('.elgg-state-selected').removeClass('elgg-state-selected');
		if ($topbarSelected.lengh) {
			$ept.find('.'+topbarSelected.attr('class').replace(' elgg-state-selected', '')).addClass('elgg-state-selected');
		}

	}
	return value;
};
elgg.register_hook_handler('history', 'display_page', elgg.history.display_page);



/**
 * Reload js of plugins
 * @return {[type]} [description]
 */
elgg.history.reload_js = function reload_js() {

	// Reload elgg core js
	//elgg.trigger_hook('init', 'system');
	elgg.ui.widgets.init();
	/*elgg.userpicker.init();
	elgg.autocomplete.init();

	// Reload plugins
	elgg.deck_river.reload();
	elgg.markdown_wiki.init();
	elgg.brainstorm.init();
	//elgg.bookmarks.reload();
	elgg.tags.init();
	elgg.workflow.reload();
	elgg.answers.init();
	elgg.decision.init();

	// Reload autocomplete elgg.userpicker.userList @todo remove it for next version. Elgg 1.8.9 don't fix it
	elgg.userpicker.userList = {};
	$('.elgg-user-picker-list li input').each(function(i, elem) {
		$(elgg.userpicker.userList).prop($(elem).val(), true);
	});
	*/

};
elgg.register_hook_handler('history', 'reload_js', elgg.history.reload_js);


// end of history lib

