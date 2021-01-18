<?php
/**
 * The wire plugin settings
 */

$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
    '#type' => 'plaintext',
    '#label' => elgg_echo('thewire:settings:embed_whitelist'),
    'name' => 'params[embed_whitelist]',
    'value' => $plugin->embed_whitelist,
    'id' => 'the-wire-whitelist',

]
);
