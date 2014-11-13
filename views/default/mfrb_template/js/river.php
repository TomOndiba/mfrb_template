
elgg.provide('elgg.river');

var linkParsed = null;
elgg.river.init = function() {

	/*
	 * Textarea
	 */
	$('body').on('keyup', '.thewire-textarea', function() {
		var expression = /https?:\/\/[-a-zA-Z0-9_.~]{1,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+,.~#?&//=]*)?/gi,
			regex = new RegExp(expression),
			urls = $(this).val().match(regex);

		// scrap first url
		// We check before if there is network which need scrapping with data-scrap
		if (urls && linkParsed != urls[0]) {
			linkParsed = urls[0];
			elgg.river.scrapToLinkBox($(this).closest('form').find('.linkbox'), linkParsed);
		}
	});

	/*
	 * Linkbox
	 */
	$('body')
	.on('click', '.linkbox .elgg-menu .elgg-icon-delete', function() { // remove all content in linkbox and hide it
		$(this).closest('.linkbox').addClass('hidden').html($('<div>', {'class': 'elgg-ajax-loader'}));
		return false;
	})
	.on('click', '.linkbox div.image-wrapper', function() { // don't add image in data sended by linkbox
		$(this).toggleClass('noimg');
		return false;
	});

	/*
	 * Notify box
	 */
	$('body').on('click', '.select2', function() {
		var $this = $(this);
		require(['select2'], function() {
			$.fn.select2.locales['fr'] = {
				formatMatches: function (matches) { return matches + " résultats sont disponibles, utilisez les flèches haut et bas pour naviguer."; },
				formatNoMatches: function () { return "Aucun résultat trouvé"; },
				formatInputTooShort: function (input, min) { var n = min - input.length; return "Saisissez " + n + " caractère" + (n == 1? "" : "s") + " supplémentaire" + (n == 1? "" : "s") ; },
				formatInputTooLong: function (input, max) { var n = input.length - max; return "Supprimez " + n + " caractère" + (n == 1? "" : "s"); },
				formatSelectionTooBig: function (limit) { return "Vous pouvez seulement sélectionner " + limit + " élément" + (limit == 1 ? "" : "s"); },
				formatLoadMore: function (pageNumber) { return "Chargement de résultats supplémentaires…"; },
				formatSearching: function () { return "Recherche en cours…"; }
			};
			$.extend($.fn.select2.defaults, $.fn.select2.locales['fr']);

			var formatResult = function(user) {
					return '<img src="'+user.avatar.tiny+'" class="float prs" height="21px"/>' + user.name;
				};

			$this.select2({
				multiple: true,
				data: {results: users, text: 'name'},
				formatResult: formatResult
			}).focus();
		});
	});

	/*
	 * River
	 */
	$('body')
	.on('click', '.elgg-river-item .elgg-menu-item-comment a', function() {
		$(this).closest('.elgg-river-item')
			.find('.elgg-form-comment-river').removeClass('hidden')
				.find('.elgg-input-plaintext').focus();
	})
	.on('click', '.elgg-river-comments-more', function() {
		var $this = $(this),
			$erc = $this.next(), // .elgg-river-comments
			$eri = $this.closest('.elgg-river-item');

		elgg.get('ajax/view/river/comments', {
			dataType: 'json',
			data: {
				date: $erc.find('li:first-child time').attr('time'),
				object_guid: $eri.data().object_guid
			},
			success: function(response) {
				var comm = elgg.handlebars('river-comment-item-template');

				$.each(response, function(i, e) {
					$erc.prepend(comm(elgg.river.format_river(e)));
				});

				var commLeft = $eri.data().comments_count - $erc.children('li').length;
				if (commLeft == 1) {
					$this.html(elgg.echo('mfrb:river:more_comment'));
				} else if (commLeft > 1) {
					$this.html(elgg.echo('mfrb:river:more_comments', [commLeft]));
				} else {
					$this.remove();
				}
			}
		});
	})
	.on({
		keydown: function(evt) {
			if (evt.shiftKey && evt.keyCode == 13) {
				$(this).closest('.elgg-form-comment-river').find('.submit-thewire-comment').click();
				return false;
			}
		},
		focus: function() {
			$(this).closest('.elgg-form-comment-river').addClass('focus');
		},
		blur: function() {
			$(this).closest('.elgg-form-comment-river').removeClass('focus');
		}
	}, '.elgg-form-comment-river .elgg-input-plaintext')
	.on('click', '.elgg-form-comment-river .submit-thewire-comment', function() {

		elgg.action('comment/save', {
			data: $.param(elgg.security.addToken($(this).parent().find('textarea, input').serializeObject())),
			success: function(response) {
				if(response.status > -1) {
					elgg.river.append_comment(response.output);
					$('.item-river-'+ response.output.container_guid +' .elgg-form-comment-river').addClass('hidden').find('.elgg-input-plaintext').val('');
				}
			},
			error: function() {

			}
		});
		return false;
	})
	.on('click', '.elgg-form-comment-river .elgg-icon-delete', function() {
		$(this).closest('.elgg-river-item')
			.find('.elgg-form-comment-river').addClass('hidden');
	});

	// Load on bottom scroll
	elgg.river.loadOnScroll();

	//$(window).scrollTop(0); // reset scroll page
	/*$(window).on('unload', function() { // beforeunload
		$(window).scrollTop(0);
	});*/
};
elgg.register_hook_handler('init', 'system', elgg.river.init);



/*
 * Hook for thewire submit. Invoked from history lib when user click on a send button.
 */
elgg.river.submit = function(name, type, params, value) {
	var form = elgg.history.data.$this.closest('form'),
		data = elgg.history.data.dataForm;

	if (form.hasClass('thewire-form')) {
		if (data.body.length < 1) {
			elgg.register_error(elgg.echo('mfrb:river:message:blank'));
		} else {
			data.link_name = form.find('.link_name').text();
			data.link_description = form.find('.link_description').text();
			data.link_picture = form.find('.link_picture').not('.noimg').children().attr('src');
			data.container_guid = elgg.get_page_owner_guid();

			elgg.action('thewire/add', {
				data: data,
				success: function(json) {
					elgg.river.prepend_river(json.output);
					form.find('.linkbox').addClass('hidden').html($('<div>', {'class': 'elgg-ajax-loader'}));
					form.find('.thewire-textarea').val('').removeAttr('style');
					linkParsed = null;
				},
				error: function(){

				}
			});
		}
		return false;
	}
	return true;
};
elgg.register_hook_handler('history', 'submit', elgg.river.submit);



/**
 * Add a river item in river feed. This river item is submited by user, or sended by nodejs.
 * @param  {array}  elem   json of river item
 */
elgg.river.prepend_river = function(elem) {
	var elem = elgg.river.format_river(elem),
		$rivers = [$('.elgg-list-river[data-page_type="all"]:not([load-river])')];

	// add mine if we are owner of the message, with subject_guid
	if (elem.subject_guid == elgg.get_logged_in_user_guid()) {
		$rivers.push($('.elgg-list-river[data-page_type="mine"]:not([load-river])'));
	}
	// add group feed with target_guid
	if ($('.elgg-group-'+elem.target_guid).length) $rivers.push($('.elgg-group-'+elem.target_guid+' .elgg-list-river'));

	$.each($rivers, function() {
		$(this).prepend(
			$(elgg.handlebars('river-item-template')(elem)).data(elem).river_highlight()
		);
	});
	/*$('.elgg-list-river[data-page_type="mine"], .elgg-list-river[data-page_type="all"]').not('[load-river]')
	.add('.elgg-group-'+elem.target_guid+' .elgg-list-river')
	.prepend(
		$(elgg.handlebars('river-item-template')(elem)).data(elem).effect('highlight', {}, 1000)
	);*/
};



/**
 * Add comment in river feed. This comment is submited by user or sended by nodejs.
 * @param  {[type]} comment comment in json format
 */
elgg.river.append_comment = function(comment) {
	var $efcr = $('.item-river-'+ comment.container_guid +' .elgg-form-comment-river'), // elgg-form-comment-river
		comm = elgg.handlebars('river-comment-item-template')(elgg.river.format_river(comment));

	$.each($efcr, function() {
		$(this).parent().find('.elgg-river-comments')
			.removeClass('hidden').append($(comm).river_highlight());
	});
};



elgg.provide('elgg.river.nodejs');
/**
 * nodejs handler for new messages
 */
elgg.river.nodejs.new_wire = function(hook, type, params, value) {
	if (params.message.subject_guid != elgg.get_logged_in_user_guid() || !elgg.visibility.active) {
		elgg.river.prepend_river(params.message);
	}
	return value;
};
elgg.register_hook_handler('nodejs', 'message:new_wire', elgg.river.nodejs.new_wire);



/**
 * nodejs handler for new comment
 */
elgg.river.nodejs.new_comment = function(hook, type, params, value) {
	if (params.message.subject.guid != elgg.get_logged_in_user_guid() || !elgg.visibility.active) {
		elgg.river.append_comment(params.message);
	}
	return value;
};
elgg.register_hook_handler('nodejs', 'message:new_comment', elgg.river.nodejs.new_comment);



/**
 * Format json river item to complete or rearrange some stuffs
 * @param  {json} elem    a river element in json format
 * @return {json}         json
 */
elgg.river.format_river = function(elem) {
	elgg.river.store_user(elem.subject);
	elem.friendlytime = elgg.friendly_time(elem.posted);
	elem.actions = {
		like: elgg.security.addToken(elgg.get_site_url()+'action/like?guid='+elem.object_guid),
		unlike: elgg.security.addToken(elgg.get_site_url()+'action/unlike?guid='+elem.object_guid),
		reply: true
	};
	elem.likes = parseInt(elem.likes);
	elem.liked = parseInt(elem.liked);
	if (elem.likes > 0) elem.likers_string = elgg.river.format_likers(elem);
	if (elem.message) elem.message = elem.message.ParseURL();
	if (!elgg.isUndefined(elem.comments)) {
		$.each(elem.comments, function(i, com) {
			com = elgg.river.format_river(com);
		});
		if (elem.comments_count == 6) elem.more_comments = elgg.echo('mfrb:river:more_comment');
		if (elem.comments_count > 6) elem.more_comments = elgg.echo('mfrb:river:more_comments', [elem.comments_count - 5]);
	}
	if (elem.summary && elgg.get_page_owner_guid() == elem.target_guid) delete elem.summary;
	return elem;
};



/**
 * Store user in a global var users
 * @param  {json} user    a user object
 */
users = [];
elgg.river.store_user = function(user) {
	if (!$.grep(users, function(e){ return e.guid === user.guid; }).length) {
		user.id = user.guid; // usefull for select2
		users.push(user);
	}
};



/**
 * Format an array of likers of an item (thewire or comments)
 * @param  {array}  elem   array of likers
 * @return {string}        sentence of who liked this object
 */
elgg.river.format_likers = function(elem) {
	var format_user = function(user) {
			return '<span class="elgg-user-info-popup" data-username="'+user.username+'" data-guid="'+user.guid+'">'+user.name+'</span>';
		};

	if (elem.likes == 1) {
		if (elem.liked) {
			return elgg.echo('likes:Ilikedthis');
		} else {
			return elgg.echo('likes:userlikedthis', [format_user(elem.likers[0])]);
		}
	} else if (elem.likes > 1) {
		var likers = [],
			IAndUs = 'userslikedthis';

		// compile likers
		$.each(elem.likers, function(i,e) {
			if (e.guid == elgg.get_logged_in_user_guid()) {
				likers.unshift(elgg.echo('likes:you'));
				IAndUs = 'IandUserslikedthis';
			} else {
				likers.push(format_user(e));
			}
		});

		// output likers
		if (elem.likes == 2) {
			return elgg.echo('likes:'+IAndUs, [elgg.echo('likes:himandhim', [likers[0], likers[1]])]);
		} else if (elem.likes == 3) {
			var colon = likers[0] +', '+ likers[1];
			return elgg.echo('likes:'+IAndUs, [elgg.echo('likes:himandhim', [colon, likers[1]])]);
		} else {
			var colon = likers[0] +', '+ likers[1] +', '+ likers[2],
				other = elem.likes-3;
			return elgg.echo('likes:'+IAndUs, [elgg.echo('likes:himandhim', [colon, elgg.echo('likes:likedbyother'+(other>1?'s':''), [other])])]);
		}
	}
};



/**
 * Load a river feed
 * @param  {[type]}   param        param that extend data queries
 * @param  {Function} callback     Function executed on success
 */
elgg.river.load_river = function(param, callback) {
	var river = $('.elgg-layout:not(.hidden) .elgg-list-river:not(.hidden)'),
		data = $.extend(river.data(), param),
		callback = callback || $.noop;

	elgg.get('activity?view=json',{
		data: data,
		dataType: 'json',
		success: function(response) {
			var itemTemplate = elgg.handlebars('river-item-template');

			Handlebars.registerPartial('comment', $('#river-comment-item-template').html());

			$.each(response, function(i, elem) {
				elem = elgg.river.format_river(elem);
				river.find('.elgg-ajax-loader').addClass('hidden').before($(itemTemplate(elem)).data(elem));
			});
			callback();

			if (response.length < 20 && !river.hasClass('single-view')) river.find('.elgg-ajax-loader').addClass('end').html(elgg.echo('mfrb:river:end'));
			if (response.length == 0) river.find('.elgg-ajax-loader').addClass('end').html(elgg.echo('mfrb:river:none'));
		}
	});
};

/* Helper to autolad river on new page */
elgg.river.autoload_river = function(name, type, params, value) {
	var river = $('.elgg-layout:not(.hidden) .elgg-list-river[load-river]:not(.hidden)');

	if (river.length) {
		elgg.river.load_river(null, function() {
			river.removeAttr('load-river');
		});
	}

	return value;
};
elgg.register_hook_handler('history', 'get_page', elgg.river.autoload_river, 490);



/*
 * Load river on bottom scroll
 */
elgg.river.loadOnScroll = function() {
	$(window).bind('scroll.river', function() {
		if(this.scrollY + $(window).height() > $(document).height() - 100) {
			var river = $('.elgg-layout:not(.hidden) .elgg-list-river:not(.hidden)'),
				loader = river.find('.elgg-ajax-loader');

			if (!loader.hasClass('end') && loader.hasClass('hidden') && !river.hasClass('single-view')) {
				loader.removeClass('hidden');
				elgg.river.load_river({
					posted: river.children('.elgg-river-item').last().data().posted
				}, function() {
					//elgg.river.loadOnScroll();
				});
			}
		}
	});
};



/**
 * Add like/unlike actions
 */
elgg.history.register_direct_action('like\\?guid=', {
	progressBar: false,
	success: function(json) {
		if (json.status > -1) {
			var item = $('.item-river-'+json.output.guid);

			item.find('.elgg-menu-item-like').addClass('hidden').next().removeClass('hidden');
			item.find('.elgg-river-likes').removeClass('hidden').html(elgg.river.format_likers(json.output));
		}
	}
});
elgg.history.register_direct_action('unlike\\?guid=', {
	progressBar: false,
	success: function(json) {
		if (json.status > -1) {
			var item = $('.item-river-'+json.output.guid);

			item.find('.elgg-menu-item-unlike').addClass('hidden').prev().removeClass('hidden');
			if (json.output.likes == 0) item.find('.elgg-river-likes').addClass('hidden');
			item.find('.elgg-river-likes').html(elgg.river.format_likers(json.output));
		}
	}
});



/**
 * Parse link and add data to linkbox
 * @param  {[type]} elem     the linkbox
 * @param  {[type]} url      the url
 */
elgg.river.scrapToLinkBox = function(elem, url) {
	var $lb = elem;
	elgg.river.scrapWebpage(url, {
		beforeSend: function() {
			$lb.removeClass('hidden');
		},
		success: function(data) {
			if (data) {

				if (!data.title || elgg.isNull(data.title)) data.title = data.url;
				data.title = $('<div>').html(data.title).text(); // decode html entities
				if (data.metatags) {
					$.grep(data.metatags, function(e) {
						if (e[0] == 'description') data.description = $('<div>').html(e[1]).text();
					});
				}
				if (data.images.length) {
					data.mainimage = data.images[0].src;
					data.images.shift();
				}
				data.src = function() {
					return this.src;
				};
				data.editable = true;

				$lb.html(elgg.handlebars('linkbox-template')(data));

				$lb.find('li.image-wrapper').click(function() {
					var $ei = $lb.find('.elgg-image'),
						first = $ei.children().first(),
						firstHtml = first.html();

					first.html(this.innerHTML);
					$(this).html(firstHtml);
					return false;
				});

				// in popup
				if ($('html').hasClass('bookmarklet')) {
					if (data.description) {
						$('textarea[name="description"]').val(data.description);
					}
					if (data.metatags) {
						$.grep(data.metatags, function(e) {
							if (e[0] == 'keywords') data.keywords = $('<div>').html(e[1]).text();
						});
						if (data.keywords) {
							var tags = '';
							$.each(data.keywords.split(','), function(i, e) {
								if (i == 10) return false;
								tags += '<li class="elgg-tag link float"><a href"#" onclick="$(\'.elgg-input-tags\').addTag(\''+$.trim(e)+'\');">'+$.trim(e)+'</a></li>';
							});
							$('.tags-in-page').removeClass('hidden').filter('ul').append(tags);
						}
					}
				}

			} else {
				$lb.addClass('hidden').html($('<div>', {'class': 'elgg-ajax-loader'}));
			}

		},
		error: function() {
			console.log('error');
		}
	});
};



/**
 * Scrap a webpage and return matatags, images and links.
 * @param  [string]            url of the webpage to parse
 * @param  [object]            options
 * @return [object]            parsed datas
 */
elgg.river.scrapWebpage = function(url, options) {
	options = $.extend({
					minSize: 120,                       // [string]            Title of the popup
					beforeSend: $.noop,                 // [function]          function will be executed just before request
					success: $.noop,                    // [function]          function will be executed when success
					error: $.noop,                      // [function]          function will be executed on error
				}, options);

	elgg.get(elgg.get_site_url() + 'mod/mfrb_template/lib/scraper.php', {
		data: {
			url: url
		},
		dataType: 'json',
		beforeSend: options.beforeSend,
		success: function(response) {

			// response.message is filled by scraper only if there is an error
			if (response.message) {
				options.success(false);
				return false;
			}

			var Images = [],
				imgsLength = response.images.length,
				nbrLoads = 0,
				imgLoaded = function(img) {
					nbrLoads++;
					if (nbrLoads >= imgsLength) {
						Images.sort(function(a, b) {
							return (a.nDim > b.nDim) ? -1 : (a.nDim < b.nDim) ? 1 : 0;
						});
						// put og:image first
						if (response.metatags) {
							$.grep(response.metatags, function(e){
								if (e[0] == 'og:image') Images.unshift({'src': e[1]});
							});
						}
						response.images = Images;

						// Scrapping ended. We execute success function
						options.success(response);
					}
				};

			if (imgsLength) {
				$.each(response.images, function(i, e) {
					var img = new Image(),
						iD = {};

					iD.src = img.src = e;
					img.onload = function() {
						iD.width = this.width;
						iD.height = this.height;
						iD.nDim = parseFloat(iD.width) * parseFloat(iD.height);
						if (options.minSize != 0 && options.minSize <= iD.width && options.minSize <= iD.height) {
							Images.push(iD);
						} else if (options.minSize == 0) {
							Images.push(iD);
						}
						imgLoaded(img);
					};
					img.onerror = function() {imgLoaded(img);};
				});
			} else {
				options.success(response);
			}
		},
		error: options.error
	});
};



/**
 * Tools
 */
$.fn.river_highlight = function() {
	$(this).effect('highlight', {}, 1000);
	return this;
}



String.prototype.ParseURL = function(reduce, videopopup) {
	return this.replace(/(.{2})?((?:https?:\/\/|www\.)[A-Za-z0-9-_]+\.[A-Za-z0-9-_:,@%&\?\/.=~+#]+)/g, function(match, pre, url) {
		if (pre == '="') return pre+url;
		if (elgg.isUndefined(pre)) pre = '';
		if (/^www/.test(url)) {
			var href = 'http://'+url;
		} else {
			var href = url;
		}
		if (reduce) {
			url = url.replace(/https?:\/\//, '');
			if (url.length > 35) url = url.substr(0, 32)+'…';
		}
		var iframeUrl = null;
		if (videopopup && (iframeUrl = elgg.deck_river.setVideoURLToIframe(href))) {
			return pre+'<a class="media-video-popup" href="'+href+'" onclick="javascript:void(0)" data-source="'+iframeUrl+'">'+url+'</a>';
		} else {
			return pre+'<a target="_blank" rel="nofollow" class="t250" href="'+href+'">'+decodeURIComponent(url)+'</a>';
		}
	});
};



String.prototype.TruncateString = function(length, more) {
	var length = length || 140,
		more = more || '[...]',
		trunc = '';

	do {
		length++;
		trunc = this.substring(0, length);
	} while (trunc.length !== this.length && trunc.slice(-1) != ' ');
	if (length+100 < this.length) {
		var rand = (Math.random()+"").replace('.','');
		return this.substring(0, length-1) +
				'<span id="text-part-'+rand+'" class="hidden">' + this.substring(length-1, this.length) + '</span>' +
				'<a rel="toggle" href="#text-part-'+rand+'"> ' + more + '</a>';
	} else {
		return this;
	}
};