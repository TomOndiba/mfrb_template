<?php
/**
 * JSON thewire river view
 *
 * @uses $vars['item']
 */

$item = $vars['item'];

echo json_encode(get_wire_object($item));

