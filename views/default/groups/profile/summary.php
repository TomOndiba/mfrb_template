<?php
/**
 * Group profile summary
 *
 * Icon and profile fields
 *
 * @uses $vars['group']
 */

if (!isset($vars['entity']) || !$vars['entity']) {
	echo elgg_echo('groups:notfound');
	return true;
}

$group = $vars['entity'];
$owner = $group->getOwnerEntity();

if (!$owner) {
	// not having an owner is very bad so we throw an exception
	$msg = "Sorry, '" . 'group owner' . "' does not exist for guid:" . $group->guid;
	throw new InvalidParameterException($msg);
}

?>
<div class="groups-profile clearfix elgg-image-block">
	<div class="elgg-image">
		<div class="groups-profile-icon">
			<?php
				// we don't force icons to be square so don't set width/height
				echo elgg_view_entity_icon($group, 'medium', array(
					'href' => '',
					'width' => '',
					'height' => '',
				));
			?>
		</div>
	</div>

	<div class="elgg-image-alt">
		<?php
			$buttons = elgg_view_menu('title', array(
				'sort_by' => 'priority',
				'class' => 'elgg-menu-hz',
				'item_class' => 'mbs float-alt'
			));
			echo $buttons;
		?>
	</div>

	<div class="groups-profile-fields elgg-body">
		<h2><?php echo ucfirst($group->name); ?></h2>
		<div class="mts">
			<?php echo elgg_view('output/longtext', array(
				'value' => $group->description
			)); ?>
		</div>
	</div>
</div>
