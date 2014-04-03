// start of history lib

elgg.provide('mrfb.history');

/*
 * Sample to understand how js is loaded.
 * You can uncomment to log time and perform benchmark.
 */


benchmarkTimeInit = new Date().getTime();
benchmarkTimeHistory = 0;
console.log(benchmarkTimeInit, 'init'); // Executed when the js loads and the user arrives for the first time on mrfb. elgg core could be not loaded.

elgg.provide('mrfb.benchmark');
mrfb.benchmark.init = function() {
	console.log(new Date().getTime()-benchmarkTimeInit, 'provide'); // Executed after elgg core js is loaded, only on the first time on mrfb
	$(document).ready(function() {
		console.log(new Date().getTime()-benchmarkTimeInit, 'ready'); // Executed when DOM is ready, only on the first time on mrfb
	});
	$(window).load(function(){ //Executed after everythings (css,js,images) are loaded, only on the first time on mrfb
		console.log(new Date().getTime()-benchmarkTimeInit, 'load');
	});

};
elgg.register_hook_handler('init', 'system', mrfb.benchmark.init);

mrfb.benchmark.click = function() { // Executed when user click on a link
	benchmarkTimeHistory = new Date().getTime();
	console.log(benchmarkTimeHistory, 'click');
};
elgg.register_hook_handler('mrfb_history', 'click', mrfb.benchmark.click);

mrfb.benchmark.statechange = function() { // Executed when url change
	console.log(new Date().getTime()-benchmarkTimeHistory, 'statechange');
};
elgg.register_hook_handler('mrfb_history', 'statechange', mrfb.benchmark.statechange);

mrfb.benchmark.success = function() { // Executed when page are loaded after a click or url change
	console.log(new Date().getTime()-benchmarkTimeHistory, 'success');
};
elgg.register_hook_handler('mrfb_history', 'success', mrfb.benchmark.success);

mrfb.benchmark.done = function() { // Executed when page are loaded and all stuff are done (reload template and js...)
	console.log(new Date().getTime()-benchmarkTimeHistory, 'done');
};
elgg.register_hook_handler('mrfb_history', 'done', mrfb.benchmark.done);




/**
 * Function to initiate full ajax.
 */
mrfb.history.init = function() {
	//var History = window.History;

	$(window).bind('statechange', function() { //History.Adapter.bind(window, 'statechange', function(event) {
		require(['history'], function() {
			var state = History.getState();
			if (state && elgg.trigger_hook('mrfb_history', 'statechange', state, true)) {
				console.log('state', state);
				mrfb.history.getPage(state.url, state.data);
			}
		});
	});

	//if (History.enabled) {
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

		// ajaxify links
		$('body').on('click',
			// :not is fastest than .not() See http://jsperf.com/jquery-css3-not-vs-not
			"a:internal:not("+
				"[href^='#'],"+
				"[href$='#'],"+
				"[rel=toggle],"+
				"[rel=popup],"+
				"[href*='/admin/'],"+
				"[href*='/ajax/'],"+
				"[href*='/logout'],"+
				"[href*='view=rss'],"+
				"[href*='address='],"+
				"[href*='/action/widgets/delete'],"+
				"[href*='notifications/personal'],"+
				"[href*='comment/edit'],"+
				".noajax,"+
				".ui-corner-all"+
			")" // autocomplete popup
		, function(evt) {
			var $this = $(this),
				href = $this.attr('href');

			// We skip if href is null, undefined or empty. In case of...
			if (elgg.isNullOrUndefined(href) || href === '') return false;

			// We check if there is confirmation. Continue if not or user accept confirm dialog.
			if ($this.hasClass('elgg-requires-confirmation') && !elgg.ui.requiresConfirmation($this)) return false;

			var params = {
				url: elgg.normalize_url(decodeURIComponent(href)),
				$this: $this,
				event: evt
			};
			$.extend(params, elgg.parse_url(params.url));

			// Plugin can hook at this point to stop click event by returning false
			if (!elgg.trigger_hook('mrfb_history', 'click', params, true)) return false;

			// Continue as normal (open link in new tab) for cmd/ctrl+click
			if (evt.which == 2 || evt.metaKey) return true;

			var url = elgg.normalize_url(decodeURIComponent(href)),
				parsedUrl = elgg.parse_url(url),
				ExecAction = function(url, callback) {
					elgg.action(url, {
						success: function(json) {
							callback();
						}
					});
				};

			// if it's an actions, do action and skip history.
			if (url.match('/action/friends/add')) {
				ExecAction(url, function() {
					var query = elgg.parse_url(url, 'query'),
						friend = query.match(/friend=\d+/)[0],
						stats = $('.user-stats li:first-child .stats');

					$('a.tooltip.add_friend[href*="'+friend+'"]').html('&#44033;'); // unicode AC01
					$('a.elgg-button.add_friend[href*="'+friend+'"]').html(elgg.echo('friend:remove'));
					$('a.add_friend[href*="'+friend+'"]')
						.blur()
						.removeClass('add_friend')
						.addClass('remove_friend')
						.attr({
							href: elgg.get_site_url() + 'action/friends/remove?' + query,
							title: elgg.echo('friend:remove')
						});
					stats.html(parseInt(stats.html())+1);
				});
			} else if (url.match('/action/friends/remove')) {
				ExecAction(url, function() {
					var query = elgg.parse_url(url, 'query'),
						friend = query.match(/friend=\d+/)[0],
						stats = $('.user-stats li:first-child .stats');

					$('a.tooltip.remove_friend[href*="'+friend+'"]').html('&#44032;'); // unicode AC00
					$('a.elgg-button.remove_friend[href*="'+friend+'"]').html(elgg.echo('friend:add'));
					$('a.remove_friend[href*="'+friend+'"]')
						.blur()
						.removeClass('remove_friend')
						.addClass('add_friend')
						.attr({
							href: elgg.get_site_url() + 'action/friends/add?' + query,
							title: elgg.echo('friend:add')
						});
					stats.html(parseInt(stats.html())-1);
				});

			// it's a link
			} else {
				var fragment = parsedUrl.fragment || false,
					path_url = parsedUrl.path,
					originUrl = elgg.normalize_url(decodeURIComponent(window.location.href)),
					path_origin = elgg.parse_url(originUrl, 'path');

				if (fragment && path_origin == path_url) { //same page, go to #hash
					if ($('#'+fragment).length) $(window).scrollTo($('#'+fragment), 'slow', {offset:-60});
				} else {
					mrfb.history.progressBar('start');
					mrfb.history.pushState({originUrl: originUrl, fragment: fragment}, null, url.split("#")[0]);
				}
			}

			return false;
		});

		// Register hook handler for some actions.
		mrfb.history.register_direct_action('/action/river/delete', function(params) {
			params.$this.closest('.elgg-item').css('background-color', '#FF7777').fadeOut();
		});
		mrfb.history.register_direct_action('/action/comments/delete', function(params) {
			$('#item-annotation-'+elgg.parse_str(params.query).annotation_id).css('background-color', '#FF7777').fadeOut();
		});

		// ajaxify submit forms
		$('body').on('click',
						"input[type=submit]:not("+
							"[id='thewire-submit-button'],"+
							"[id='button-signin'],"+
							"[class*='noajax'])"
		, function(evt) {

			elgg.trigger_hook('mrfb_history', 'submit');
			mrfb.history.progressBar('start');

			var form = $(this).closest('form'),
				dataForm = form.serialize(),
				replaceHighlight = function(elem, t) {
					elem.effect("highlight", {}, 3000)
						.find('.elgg-output').replaceWith($('<div>', {'class': 'elgg-output markdown-body'}).html(elgg.markdown_wiki.ShowdownConvert(t)));
					elem.find('pre code').each(function(i, e) {
						if (e.className == '') $(e).addClass('no-highlight');
						hljs.highlightBlock(e);
					});
				};

			if (!elgg.isUndefined(form.data('validator'))) { // check if form has jquery.validate handler and if it's a valid form
				if (!form.valid()){
					elgg.register_error(elgg.echo('forms:not_valid'));
					return false;
				}
			}

			if (form.hasClass('elgg-form-login')) { // redirect for login
				return true;
			} else if (form.hasClass('elgg-form-editablecomments-edit')) { // Special for editable comment

				elgg.action('editablecomments/edit', {
					data: dataForm,
					success: function(json) {
						var annotation_id = form.find('input[name=annotation_id]').val();

						$('#editablecomments-edit-annotation-'+annotation_id).toggle();
						replaceHighlight($('#item-annotation-'+annotation_id), json.output);
					}
				});

			} else if (form.hasClass('elgg-form-comments-add')) { // Special for live comment

				elgg.action('livecomments/add', {
					data: dataForm,
					success: function(json) {
						var orderBy = form.hasClass('desc') ? 'desc' : 'asc',
							comBlock = form.closest('.elgg-comments'),
							ul = comBlock.find('ul.elgg-list-annotation'),
							li = $(json.output).find('li:first'),
							txt = li.find('.elgg-output').html(),
							liID = li.attr('id');

						if (orderBy ==  'asc') {
							if (ul.length < 1) {
								comBlock.prepend(json.output);
								if (!form.hasClass('tiny')) comBlock.prepend($('<h3>', {id: 'comments', 'class': 'gwfb pbs'}).html(elgg.echo('comments')));
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
						form.find('textarea').val('').height(190);
						form.find('.preview-markdown').html('').height(178);
						elgg.markdown_wiki.editor();
					}
				});

			} else if (form.hasClass('elgg-form-answers-answer-edit')) { // Special for edit answer

				elgg.action('answers/answer/edit', {
					data: dataForm,
					success: function(response) {
						if (response.status == 0) {
							var answer_guid = form.find('input[name=answer_guid]').val(),
								answer_text = form.find('textarea[name=answer_text]').val();
							replaceHighlight($('#elgg-object-'+answer_guid+' .answer-output'), answer_text);
							$('#elgg-object-'+answer_guid+' .elgg-menu-item-edit a').click();
						}
					}
				});

			} else { // ajaxify others forms

				var url = elgg.normalize_url(decodeURIComponent(form.attr('action'))),
					url_origin = elgg.normalize_url(decodeURIComponent(window.location.href)),
					data = {dataForm: dataForm};

				// This is a misuse of elgg.security.addToken() because it is not always a
				// full query string with a ?. As such we need a special check for the tokens.
				/*if (!elgg.isString(data.dataForm) || data.dataForm.indexOf('__elgg_ts') == -1) {
					data.dataForm = elgg.security.addToken(data.dataForm);
				}*/

				mrfb.history.pushState({origin: url_origin, dataForm: dataForm}, null, url);
				//mrfb.history.getPage(url, data);

			}

			return false;
		});

	//}

};
elgg.register_hook_handler('init', 'system', mrfb.history.init);



/**
* Helper to register some actions who perform event and stop click at history click hook.
* @param  {string}      match       Check if url match this string.
* @param  {function}    callback    Function to execute after elgg.action. Parameters for this function is (params, json).
* @param  {integer}     priority    Priority of the hook handler.
* @return {bool}                    Return false if action is executed or original value.
*/
mrfb.history.register_direct_action = function(match, callback, priority) {
	var executeAction = function(name, type, params, value) {
		if (params.url.match(match)) {
			mrfb.history.progressBar('start');
			elgg.action(params.url, {
				success: function(json) {
					mrfb.history.progressBar('stop');
					callback(params, json);
				}
			});
			return false;
		} else {
			return value;
		}
	};

	elgg.register_hook_handler('mrfb_history', 'click', executeAction, priority);
};


/**
* Ajaxified website. Get page called by link or submit form.
* @param  {[type]} url
* @param  {[type]} data
* @return {[type]}
*/
mrfb.history.getPage = function(url, data) {
	var data = data || false,
		fragment = data.fragment || false,
		urlActivity = elgg.get_site_url() +'activity(.*)',
		activityTab = url.match(urlActivity),
		urlToStashID = function(match) {
			if (!match) return false;
			return match[1].replace(/^\//, '').replace(/\s/, '');
		},
		stashDeck = function() { // stash deck river before change elgg-page-body
			if ($('body').hasClass('fixed-deck')) {
				var deckOrigin = data.origin.replace(/#$/, '');

				$('.elgg-menu-item-logo a').attr('href', deckOrigin);
				$('.elgg-river-layout:not(.hidden)').addClass('hidden').attr('id', 'stash_'+urlToStashID(deckOrigin.match(urlActivity)));
			}
		};

	// if user go back to the deck-river and river is stashed, we show it and skip elgg.get
	/*if (activityTab && $('#stash_'+urlToStashID(activityTab)).length) {
		var $stash = $('#stash_'+urlToStashID(activityTab));

		stashDeck();
		$('.elgg-layout:not(.hidden)').remove();
		$stash.removeClass('hidden');
		$('body').attr('class', 't25 fixed-deck'); // we replace class remove all other class
		$('.deck-popup').not('.pinned').remove(); // remove non-pinned popup
		if (data.callback) data.callback();
		return true;
	}*/

	elgg.get(url, {
		data: data.dataForm, // @todo Here it could be usefull to add some infos about browser type, screen size...
		dataType: 'json',
		complete: function() {
			console.log('complete');
			mrfb.history.progressBar('stop');
		},
		success: function(response, textStatus, xmlHttp) {

			elgg.trigger_hook('mrfb_history', 'success');

			var urlParsed = elgg.parse_url(url),
				urlPath = urlParsed.path;

			//try {

				eval(response.js_code); // execute javascript from mrfb_execute_js. It's include hack to reload js/initialize_elgg forked from mrfb_template/page/reinitialize_elgg
				elgg.register_error(response.system_messages.error);
				elgg.system_message(response.system_messages.success);

				if (response.forward_url && !response.system_messages.error.length) {
					// This is an action !
					// If it's not an action, response doesn't got a forward_url
					// note: when server is down > Object { readyState=0, status=0, statusText="error"}
					forward_url = elgg.normalize_url(decodeURIComponent(response.forward_url));

					if (urlPath.match('/action/groups/featured') || urlPath.match('/action/groups/leave')) {
						mrfb.history.replaceState(data, null, data.origin);
					} else if (forward_url != null) {
						if (urlPath.match('/action/brainstorm/delete')) {
							var brainstorm_guid = elgg.parse_str(urlParsed.query).guid;
							$('.elgg-body #elgg-object-'+brainstorm_guid).css('background-color', '#FF7777').fadeOut();
						}
						mrfb.history.replaceState(data, null, forward_url); // catch forward(). See mrfb_ajax_forward_hook
					} else if (xmlHttp.status = 200) {
						window.location.replace(url); // in case of...
					}

				} else if (!response.forward_url) { // So this is a page

					var $respBody = $(response.body),
						orignParsed = data.origin ? elgg.parse_url(data.origin) : false,
						urlOffset = !elgg.isUndefined(urlParsed.query) ? urlParsed.query.match(/offset=(\d+)/) : false;

					$('title').html(response.title);

					if (urlOffset && orignParsed.path == urlPath) { // same url. Only query offset change, we just slide page body.
						var numOrigin = elgg.isUndefined(orignParsed.query) ? 0 : parseInt(orignParsed.query.match(/offset=(\d+)/)[1]),
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

					} else {
						var $epb = $('.elgg-page-body > .elgg-inner');
						//stashDeck();
						$epb.children().not('.elgg-layout.hidden').remove();
						$epb.append($respBody.fadeIn());
						$('.elgg-menu-site, .elgg-menu-topbar').remove();
						$('.elgg-page-topbar > .elgg-inner').append($(response.topbar));
						$('.elgg-popup').not('.pinned').remove(); // remove non-pinned popup

					}

					if (!data.noscroll) $(window).scrollTop(0);

					mrfb.history.reloadJsFunctions();
				}

				if (fragment && $('#'+fragment).length) {
					$(window).scrollTo($('#'+fragment), 'slow', {offset:-60});
				}

				if (data.callback) data.callback();

			//} catch (err) { // So this is a page
			//	console.log(err, 'error');
			//}
		},
		error: function(response) {
			console.log(response, 'error');
			mrfb.history.progressBar('stop');
			if (response.status != 404) mrfb.history.changeUrl(data);
		},
		statusCode: {
			404: function() {
				console.log('4Q4');
			},
			500: function() {
				console.log('500');
			}
		}
		/*error: function(jqXHR, textStatus, errorThrown){
			document.location.href = url;
			return false;
		}*/
	});
};



/**
 * Display page
 */
mrfb.history.displayPage = function(response) {

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
 * Wrapper for History.replaceState
 */
mrfb.history.replaceState = function(data, title, url) {
	require(['history'], function() {
		History.replaceState(data, title, url);
	});
};



/**
 * Wrapper for History.pushState
 */
mrfb.history.pushState = function(data, title, url) {
	require(['history'], function() {
		History.pushState(data, title, url);
	});
};



/**
 * Change url in browser without load the page. Only url is changed.
 * To do that, we need to register a hook on mrfb_history statechane to return true, and remove it just after.
 *
 * @param {Obj}      data     data of History State
 * @param {String}   url      Url to put in browser url (optional). If no url is provided, data.origin is the url.
 */
mrfb.history.changeUrl = function(data, url) {
	var url = url || data.origin;
	elgg.register_hook_handler('mrfb_history', 'statechange', mrfb.history.interceptHistory);
	mrfb.history.replaceState(data, null, url);
};
// the hook that return false and remove himself
mrfb.history.interceptHistory = function() {
	elgg.unregister_hook_handler('mrfb_history', 'statechange', mrfb.history.interceptHistory);
	return false;
};



/**
 * Progress bar
 */
mrfb.history.progressBarInterval = null;
mrfb.history.progressBar = function(action) {
	var $b = $('body'),
		$p = $('#progress'),
		aL = 'ajaxLoading';

	if (action == 'start') {
		$b.addClass(aL);
		$p.css({width: 0});
		if (!mrfb.history.progressBarInterval) {
			mrfb.history.progressBarInterval = setInterval(function() {
				var windowWidth = $(window).width(),
					width = Math.min($p.width() + Math.floor(Math.random() * (windowWidth*0.2) + 50), windowWidth*0.9);
				$p.animate({width: width}, 250);
			}, 300);
		}
	} else if (action == 'stop') {
		clearInterval(mrfb.history.progressBarInterval);
		mrfb.history.progressBarInterval = null;
		$p.animate({width: '100%'}, 100);
		$b.removeClass(aL);
	}
};



/**
 * Reload js of plugins
 * @return {[type]} [description]
 */
mrfb.history.reloadJsFunctions = function() {
	$('.tipsy').remove(); // in case of because sometimes tooltip stick

	// Send to Piwik tracker
	if (typeof piwikTracker != 'undefined' && typeof piwikTracker.trackPageView == 'function') {
		piwikTracker.setDocumentTitle(document.title);
		piwikTracker.setCustomUrl(window.location.href);
		piwikTracker.trackPageView();
	}

	// Reload elgg core js
//	elgg.trigger_hook('init', 'system');
	elgg.ui.widgets.init();
	elgg.eaam.init();
/*	elgg.userpicker.init();
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
	elgg.trigger_hook('mrfb_history', 'done');
}

// end of history lib

