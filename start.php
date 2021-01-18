<?php

include 'vendor/autoload.php';
use Embera\Embera;

/**
 * The wire pro
 *
 * Forked from core the wire
 *
 */

/**
 * The Wire initialization
 *
 * @return void
 */
function thewirepro_init()
{
    elgg_set_entity_class('object', 'thewire', \ElggWirePro::class) .
    elgg_register_plugin_hook_handler('config', 'comments_latest_first', function (\Elgg\Hook $hook) {
        return false;
    });

    // remove edit and access and add thread, reply, view previous
    elgg_register_plugin_hook_handler('register', 'menu:entity', 'thewirepro_setup_entity_menu_items');
    //elgg_register_plugin_hook_handler('register', 'menu:social', 'thewirepro_setup_social_menu_items');

    // Extend system CSS with our own styles, which are defined in the thewire/css view
    elgg_extend_view('elgg.css', 'thewirepro/css');

}

function thewirepro_setup_entity_menu_items(\Elgg\Hook $hook)
{

    $entity = $hook->getEntityParam();
    if (!$entity instanceof \ElggWire) {
        return;
    }

    $menu = $hook->getValue();
    $menu->remove('edit');
    $menu->remove('reply');
    $menu->remove('thread');

    $comment_enabled = elgg_get_plugin_setting('reply_as_comment', 'thewirepro', 'yes');

    if ($comment_enabled != 'yes') {
        if (elgg_is_logged_in()) {
            $menu->add(ElggMenuItem::factory([
                'name' => 'reply',
                'icon' => 'reply',
                'text' => elgg_echo('reply'),
                'href' => elgg_generate_entity_url($entity, 'reply'),
            ]));
        }

        $menu->add(ElggMenuItem::factory([
            'name' => 'thread',
            'icon' => 'comments-o',
            'text' => elgg_echo('thewire:thread'),
            'href' => elgg_generate_url('collection:object:thewire:thread', [
                'guid' => $entity->wire_thread,
            ]),
        ]));
    }

    return $menu;
}

/**
 * Replace urls, hash tags, and @'s by links
 *
 * @param string $text The text of a post
 *
 * @return string
 */
function thewirepro_filter($text)
{
    $text = ' ' . $text;

    // email addresses
    $text = preg_replace(
        '/(^|[^\w])([\w\-\.]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})/i',
        '$1<a href="mailto:$2@$3">$2@$3</a>',
        $text);

    // links
    // preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $match);
    $config = [
        'responsive' => true,
    ];
    $embera = new Embera($config);
    $text = $embera->autoEmbed($text);

    $text = parse_urls($text);

    // usernames
    $text = preg_replace(
        '/(^|[^\w])@([\p{L}\p{Nd}._]+)/u',
        '$1<a href="' . elgg_get_site_url() . 'thewire/owner/$2">@$2</a>',
        $text);

    // hashtags
    $text = preg_replace(
        '/(^|[^\w])#(\w*[^\s\d!-\/:-@]+\w*)/',
        '$1<a href="' . elgg_get_site_url() . 'thewire/tag/$2">#$2</a>',
        $text);

    return trim($text);
}

return function () {
    elgg_register_event_handler('init', 'system', 'thewirepro_init');
};
