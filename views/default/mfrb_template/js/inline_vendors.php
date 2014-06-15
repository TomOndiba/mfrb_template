
// wrapper for scrollTo library
$.fn.scrollTo = function scrollTo(target, options, callback) {
	require(['scrollTo'], function() {
		$(this).scrollTo(target, options, callback);
	});
};



$.fn.extend({
	getCaretPosition: function(absolute) {
		// The properties that we copy into a mirrored div.
		// Note that some browsers, such as Firefox,
		// do not concatenate properties, i.e. padding-top, bottom etc. -> padding,
		// so we have to do every single property specifically.
		var properties = [
				'boxSizing',
				'width',  // on Chrome and IE, exclude the scrollbar, so the mirror div wraps exactly as the textarea does
				'height',
				'overflowX',
				'overflowY',  // copy the scrollbar for IE

				'borderTopWidth',
				'borderRightWidth',
				'borderBottomWidth',
				'borderLeftWidth',

				'paddingTop',
				'paddingRight',
				'paddingBottom',
				'paddingLeft',

				// https://developer.mozilla.org/en-US/docs/Web/CSS/font
				'fontStyle',
				'fontVariant',
				'fontWeight',
				'fontStretch',
				'fontSize',
				'lineHeight',
				'fontFamily',

				'textAlign',
				'textTransform',
				'textIndent',
				'textDecoration',  // might not make a difference, but better be safe

				'letterSpacing',
				'wordSpacing'
			],
			isFirefox = !(window.mozInnerScreenX == null),
			mirrorDiv, computed, style;

		var mirrorDiv, computed, style;

		getCaretCoordinates = function (textarea, position) {
			// mirrored div
			mirrorDiv = $('#textarea-mirror-div')[0];
			if (!mirrorDiv) {
				mirrorDiv = document.createElement('div');
				mirrorDiv.id = 'textarea-mirror-div';
				$('body').append(mirrorDiv);
			}

			style = mirrorDiv.style;
			computed = getComputedStyle(textarea);

			// default textarea styles
			style.whiteSpace = 'pre-wrap';
			style.wordWrap = 'break-word';

			// position off-screen
			style.position = 'absolute';  // required to return coordinates properly
			style.top = textarea.offsetTop + parseInt(computed.borderTopWidth) + 'px';
			style.left = '-9999px';

			// transfer textarea properties to the div
			properties.forEach(function (prop) {
				style[prop] = computed[prop];
			});

			if (isFirefox) {
				style.width = parseInt(computed.width) - 2 + 'px'  // Firefox adds 2 pixels to the padding - https://bugzilla.mozilla.org/show_bug.cgi?id=753662
				// Firefox lies about the overflow property for textareas: https://bugzilla.mozilla.org/show_bug.cgi?id=984275
				if (textarea.scrollHeight > parseInt(computed.height))
					style.overflowY = 'scroll';
			} else {
				style.overflow = 'hidden';  // for Chrome to not render a scrollbar; IE keeps overflowY = 'scroll'
			}

			mirrorDiv.textContent = textarea.value.substring(0, position);

			var span = document.createElement('span');
			// Wrapping must be replicated *exactly*, including when a long word gets
			// onto the next line, with whitespace at the end of the line before (#7).
			// The  *only* reliable way to do that is to copy the *entire* rest of the
			// textarea's content into the <span> created at the caret position.
			span.textContent = textarea.value.substring(position);
			span.style.backgroundColor = 'grey';
			mirrorDiv.appendChild(span);

			var coordinates = {
				top: span.offsetTop + parseInt(computed['borderTopWidth']),  // different ways of accessing computed's members
				left: span.offsetLeft + parseInt(computed.getPropertyValue('border-left-width'))
			};

			if (absolute) {
				var offS = $(textarea).offset();
				coordinates = {
					top: offS.top - textarea.scrollTop + coordinates.top,
					left: offS.left - textarea.scrollLeft + coordinates.left
				}

			}

			return coordinates;
		}

		return getCaretCoordinates(this[0], this[0].selectionEnd);
	}
});


/*
var textarea = document.querySelector('textarea');
		var fontSize = getComputedStyle(textarea).getPropertyValue('font-size');
var rect = document.createElement('div');
		document.body.appendChild(rect);
		rect.style.position = 'absolute';
		rect.style.backgroundColor = 'red';
		rect.style.height = fontSize;
		rect.style.width = '1px';

		['keyup', 'click', 'scroll'].forEach(function (event) {
		 textarea.addEventListener(event, update);
		});

		function update() {
		var coordinates = getCaretCoordinates(textarea, textarea.selectionEnd);
		console.log('(top, left) = (%s, %s)', coordinates.top, coordinates.left);
		rect.style.top = textarea.offsetTop
		- textarea.scrollTop
		+ coordinates.top
		+ 'px';
		rect.style.left = textarea.offsetLeft
		- textarea.scrollLeft
		+ coordinates.left
		+ 'px';
		}
*/

/**
 * jQuery plugin for getting position of cursor in textarea

 * @license under GNU license
 * @author Bevis Zhao (i@bevis.me, http://bevis.me)
 *
$(function() {

	var calculator = {
		// key styles
		primaryStyles: ['fontFamily', 'fontSize', 'fontWeight', 'fontVariant', 'fontStyle',
			'paddingLeft', 'paddingTop', 'paddingBottom', 'paddingRight',
			'marginLeft', 'marginTop', 'marginBottom', 'marginRight',
			'borderLeftColor', 'borderTopColor', 'borderBottomColor', 'borderRightColor',
			'borderLeftStyle', 'borderTopStyle', 'borderBottomStyle', 'borderRightStyle',
			'borderLeftWidth', 'borderTopWidth', 'borderBottomWidth', 'borderRightWidth',
			'line-height', 'outline'],

		specificStyle: {
			'word-wrap': 'break-word',
			'overflow-x': 'hidden',
			'overflow-y': 'auto'
		},

		simulator : $('<div id="textarea_simulator"/>').css({
				position: 'absolute',
				top: 0,
				left: 0,
				visibility: 'hidden'
			}).appendTo(document.body),

		toHtml : function(text) {
			return text.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g, '<br>&nbsp;')
				.split(' ').join('<span style="white-space:prev-wrap">&nbsp;</span>');
		},
		// calculate position
		getCaretPosition: function() {
			var cal = calculator, self = this, element = self[0], elementOffset = self.offset();

			// IE has easy way to get caret offset position
			if ($.browser.msie) {
				// must get focus first
				element.focus();
					var range = document.selection.createRange();
					$('#hskeywords').val(element.scrollTop);
					return {
							left: range.boundingLeft - elementOffset.left,
							top: parseInt(range.boundingTop) - elementOffset.top + element.scrollTop
						+ document.documentElement.scrollTop + parseInt(self.getComputedStyle("fontSize"))
					};
			}
			cal.simulator.empty();
			// clone primary styles to imitate textarea
			$.each(cal.primaryStyles, function(index, styleName) {
				self.cloneStyle(cal.simulator, styleName);
			});

			// caculate width and height
			cal.simulator.css($.extend({
				'width': self.width(),
				'height': self.height()
			}, cal.specificStyle));

			var value = self.val(), cursorPosition = self.getCursorPosition();
			var beforeText = value.substring(0, cursorPosition),
				afterText = value.substring(cursorPosition);

			var before = $('<span class="before"/>').html(cal.toHtml(beforeText)),
				focus = $('<span class="focus"/>'),
				after = $('<span class="after"/>').html(cal.toHtml(afterText));

			cal.simulator.append(before).append(focus).append(after);
			var focusOffset = focus.offset(), simulatorOffset = cal.simulator.offset();
			// alert(focusOffset.left  + ',' +  simulatorOffset.left + ',' + element.scrollLeft);
			return {
				top: focusOffset.top - simulatorOffset.top - element.scrollTop,
					// calculate and add the font height except Firefox
					//+ ($.browser.mozilla ? 0 : parseInt(self.getComputedStyle("fontSize"))),
				left: focus[0].offsetLeft -  cal.simulator[0].offsetLeft - element.scrollLeft
			};
		}
	};

	$.fn.extend({
		getComputedStyle: function(styleName) {
			if (this.length == 0) return;
			var thiz = this[0];
			var result = this.css(styleName);
			result = result || ($.browser.msie ?
				thiz.currentStyle[styleName]:
				document.defaultView.getComputedStyle(thiz, null)[styleName]);
			return result;
		},
		// easy clone method
		cloneStyle: function(target, styleName) {
			var styleVal = this.getComputedStyle(styleName);
			if (!!styleVal) {
				$(target).css(styleName, styleVal);
			}
		},
		cloneAllStyle: function(target, style) {
			var thiz = this[0];
			for (var styleName in thiz.style) {
				var val = thiz.style[styleName];
				typeof val == 'string' || typeof val == 'number'
					? this.cloneStyle(target, styleName)
					: NaN;
			}
		},
		getCursorPosition : function() {
					var thiz = this[0], result = 0;
					if ('selectionStart' in thiz) {
							result = thiz.selectionStart;
					} else if('selection' in document) {
						var range = document.selection.createRange();
						if (parseInt($.browser.version) > 6) {
								thiz.focus();
								var length = document.selection.createRange().text.length;
								range.moveStart('character', - thiz.value.length);
								result = range.text.length - length;
						} else {
									var bodyRange = document.body.createTextRange();
									bodyRange.moveToElementText(thiz);
									for (; bodyRange.compareEndPoints("StartToStart", range) < 0; result++)
										bodyRange.moveStart('character', 1);
									for (var i = 0; i <= result; i ++){
											if (thiz.value.charAt(i) == '\n')
													result++;
									}
									var enterCount = thiz.value.split('\n').length - 1;
					result -= enterCount;
										return result;
						}
					}
					return result;
			},
		getCaretPosition: calculator.getCaretPosition
	});
});


/*
 * set cursor position in textarea
 */
$.fn.setCursorPosition = function(pos) {
	if ($(this).get(0).setSelectionRange) {
		$(this).get(0).setSelectionRange(pos, pos);
	} else if ($(this).get(0).createTextRange) {
		var range = $(this).get(0).createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
}
