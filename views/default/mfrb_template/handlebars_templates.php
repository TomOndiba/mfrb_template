<?php
	$site_url = elgg_get_site_url();
?>

<!-- sound for notification -->
<audio id="beep-audio" class="hidden">
<source src="<?php echo $site_url; ?>mod/mfrb_template/graphics/sounds/mfrb-wet.mp3">
<source src="<?php echo $site_url; ?>mod/mfrb_template/graphics/sounds/mfrb-wet.wav">
<source src="<?php echo $site_url; ?>mod/mfrb_template/graphics/sounds/mfrb-wet.ogg">
</audio>

<!-- Template for linkbox -->
<script id="linkbox-template" type="text/x-handlebars-template">
	<div class="elgg-image-block clearfix">
		{{#if mainimage}}
		<ul class="elgg-image">
			<div class="link_picture image-wrapper center tooltip sw t25 gwfb" title="<?php echo elgg_echo('deck_river:linkbox:hidepicture'); ?>">
				<img height="80px" src="{{mainimage}}">
			</div>
			{{#images}}
				<li class="image-wrapper center t25"><img height="80px" src="{{src}}"></li>
			{{/images}}
		</ul>
		{{/if}}
		<div class="elgg-body pts">
			<ul class="elgg-menu elgg-menu-entity elgg-menu-hz float-alt">
				<span class="elgg-icon elgg-icon-delete link"></span>
			</ul>
			<div class="">
				<h4 class="link_name pas mrl" {{#if editable}}contenteditable="true"{{/if}}>{{title}}</h4>
				{{#if url}}
				<div class="elgg-subtext pls">{{url}}</div>
				{{/if}}
				<input type="hidden" name="link_url" value="{{url}}">
				<div class="link_description pas" {{#if editable}}contenteditable="true"{{/if}}>{{description}}</div>
			</div>
		</div>
	</div>
</script>


<!-- Template for river-item -->
<script id="river-item-template" type="text/x-handlebars-template">
<li class="item-river-{{object_guid}} elgg-river-item ptl pbm">
	<div class="elgg-image-block clearfix">
		<div class="elgg-image">
			<div class="elgg-avatar elgg-avatar-small">
				<span class="elgg-icon-hover-menu elgg-icon"></span>
				<ul class="elgg-menu elgg-menu-hover">
					<li>
						<a href="{{subject.url}}"><span class="elgg-heading-basic">@{{subject.username}}</span>{{subject.name}}</a>
					</li>
					<li>
						<ul class="elgg-menu elgg-menu-hover-actions">
							<li class="elgg-menu-item-avatar-edit">
								<a href="<?php echo $site_url; ?>avatar/edit/{{subject.username}}" class="elgg-menu-content">Modifier mon avatar</a>
							</li>
							<li class="elgg-menu-item-profile-edit">
								<a href="{{subject.url}}/edit" class="elgg-menu-content">Modifier mon profil</a>
							</li>
						</ul>
					</li>
				</ul>
				<a href="{{subject.url}}" class="">
					<img src="{{subject.avatar.small}}" alt="{{subject.username}}" title="{{subject.username}}" class="">
				</a>
			</div>
		</div>
		<div class="elgg-body">
			<div class="elgg-river-summary">
				<a href="{{subject.url}}" class="elgg-river-subject">{{subject.name}}</a>&nbsp;{{{summary}}}
				<a href="<?php echo $site_url; ?>message/view/{{object_guid}}">
					<span class="elgg-friendlytime">
						<time title="%pm %28 %b %2014 à %CEST" datetime="{{posted}}" time="{{posted}}">{{friendlytime}}</time>
					</span>
				</a>
			</div>
			<div class="elgg-river-message">{{{message}}}</div>
			{{#if attachment}}
			<a class="elgg-river-linkbox clearfix" target="_blank" href="{{attachment.link_url}}">
				<div class="elgg-river-image">
					{{#if attachment.link_picture}}<div class="elgg-image float" style="background-image: url({{attachment.link_picture}});"></div>{{/if}}
					<div class="elgg-body pam">
						<h4>{{{attachment.link_name}}}</h4>
						<span class="elgg-subtext">{{attachment.link_url}}</span>
						<div class="pts">{{{attachment.link_description}}}</div>
					</div>
				</div>
			</a>
			{{/if}}
			{{#if likes}}
			<div class="elgg-river-likes fi-like pts pbrm">{{{likers_string}}}</div>
			{{else}}
			<div class="elgg-river-likes fi-like pts pbrm hidden"></div>
			{{/if}}
			{{#if actions}}
			<ul class="elgg-menu elgg-menu-river elgg-menu-hz elgg-menu-river-default">
				<li class="elgg-menu-item-like prm {{#if liked}}hidden{{/if}}">
					<a href="{{actions.like}}" class="elgg-menu-content t250"><?php echo elgg_echo('likes:likethis'); ?></a>
				</li>
				<li class="elgg-menu-item-unlike prm {{#unless liked}}hidden{{/unless}}">
					<a href="{{actions.unlike}}" class="elgg-menu-content t250"><?php echo elgg_echo('likes:remove'); ?></a>
				</li>
				<li class="elgg-menu-item-comment prm">
					<a href="#" class="elgg-menu-content t250"><?php echo elgg_echo('comment:this'); ?></a>
				</li>
			</ul>
			{{/if}}
			<div class="elgg-river-responses pts">
				{{#if more_comments}}
				<div class="elgg-river-comments-more pas link">{{more_comments}}</div>
				{{/if}}
				<ul class="elgg-river-comments {{#unless comments}}hidden{{/unless}}">
				{{#each comments}}
					{{> comment}}
				{{/each}}
				</ul>
				<div class="elgg-form-comment-river ptm hidden">
					<input type="hidden" name="entity_guid" value="{{object_guid}}">
					<textarea rows="1" name="generic_comment" class="elgg-input-plaintext mtm" autoresize></textarea>
					<a class="elgg-button elgg-button-submit submit-thewire-comment float-alt mts"><?php echo elgg_echo('comment:this'); ?></a>
					<span class="elgg-icon elgg-icon-delete link float-alt mtm mrs"></span>
				</div>
				<div class="elgg-menu-item-comment">
					<a href="#" class="elgg-menu-content pvm phs t250"><?php echo elgg_echo('comment:this'); ?></a>
				</div>
			</div>
		</div>
	</div>
</li>
</script>


<!-- Template for river-item -->
<script id="river-comment-item-template" type="text/x-handlebars-template">
<li class="elgg-item item-river-{{object_guid}} elgg-item-object elgg-item-object-comment">
<div class="elgg-image-block clearfix">
	<div class="elgg-image">
		<div class="elgg-avatar elgg-avatar-tiny">
			<span class="elgg-icon-hover-menu  elgg-icon"></span>
			<a href="{{subject.url}}" class="">
				<img src="{{subject.avatar.tiny}}" alt="{{subject.username}}" title="{{subject.username}}" class="">
			</a>
		</div>
	</div>
	<div class="elgg-body">
		<ul class="elgg-menu elgg-menu-entity elgg-menu-hz float-alt elgg-menu-entity-default">
			<li class="elgg-menu-item-report-spam">
				<a href="#" title="Mark as spam" class="elgg-menu-content"></a>
			</li>
		</ul>
		<a href="{{subject.url}}" class="elgg-river-subject">{{subject.name}}</a>&nbsp;&nbsp;
		<span class="elgg-subtext">
			<span class="elgg-friendlytime">
				<time title="%pm %28 %b %2014 à %CEST" datetime="{{posted}}" time="{{posted}}">{{friendlytime}}</time>
			</span>
		</span>
		<div class="elgg-river-message">{{{message}}}</div>
		{{#if likes}}
		<div class="elgg-river-likes fi-like pbrs">{{{likers_string}}}</div>
		{{else}}
		<div class="elgg-river-likes fi-like pbrs hidden"></div>
		{{/if}}
		{{#if actions}}
		<ul class="elgg-menu elgg-menu-river elgg-menu-hz elgg-menu-river-default">
			<li class="elgg-menu-item-like {{#if liked}}hidden{{/if}}">
				<a href="{{actions.like}}" class="elgg-menu-content t250"><?php echo elgg_echo('likes:likethis'); ?></a>
			</li>
			<li class="elgg-menu-item-unlike {{#unless liked}}hidden{{/unless}}">
				<a href="{{actions.unlike}}" class="elgg-menu-content t250"><?php echo elgg_echo('likes:remove'); ?></a>
			</li>
		</ul>
		{{/if}}
	</div>
</div>
</li>
</script>

