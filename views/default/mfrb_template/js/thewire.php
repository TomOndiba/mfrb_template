
elgg.provide('elgg.thewire');

var linkParsed = null;
elgg.thewire.init = function() {

	$('body').on('keyup', '#thewire-textarea', function() {
		var expression = /https?:\/\/[-a-zA-Z0-9_.~]{1,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+,.~#?&//=]*)?/gi,
			regex = new RegExp(expression),
			urls = $(this).val().match(regex);

		// scrap first url
		// We check before if there is network which need scrapping with data-scrap
		if (urls && linkParsed != urls[0]) {
			linkParsed = urls[0];
			elgg.thewire.scrapToLinkBox(linkParsed);
		}
	});

	// remove all content in linkbox and hide it
	$('body').on('click', '#linkbox .elgg-menu .elgg-icon-delete', function() {
		$('#linkbox').addClass('hidden').html($('<div>', {'class': 'elgg-ajax-loader'}));
		return false;
	});

};
elgg.register_hook_handler('init', 'system', elgg.thewire.init);



/*
 * Hook for thewire submit
 */
elgg.thewire.submit = function(name, type, params, value) {
	var form = elgg.history.data.$this.closest('form'),
		data = elgg.history.data.dataForm;

	if (form.hasClass('thewire-form')) {
		console.log(form, form.find('.link_name').html());
		data.link_name = form.find('.link_name').html();
		data.link_description = form.find('.link_description').html();
		data.link_picture = form.find('.link_picture').not('.noimg').children().attr('src');

		console.log(data);
		return false;
	}
	return true;
};
elgg.register_hook_handler('history', 'submit', elgg.thewire.submit);



/**
 * Parse link and add data to linkbox
 * @param  {[type]} url the url
 */
elgg.thewire.scrapToLinkBox = function(url) {
	var $lb = $('#linkbox');
	elgg.thewire.scrapWebpage(url, {
		beforeSend: function() {
			$lb.removeClass('hidden');
		},
		success: function(data) {
			if (data) {
				console.log(data);
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

				$lb.html(Mustache.render($('#linkbox-template').html(), data));

				$lb.find('li.image-wrapper').click(function() {
					var $ei = $('#linkbox .elgg-image'),
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
elgg.thewire.scrapWebpage = function(url, options) {
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