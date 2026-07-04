<?php
/* ====================
[BEGIN_COT_EXT]
Code=seoforums
Name=SEO Forums
Category=content-seo
Description=SEO enhancements for the Forums module: smart meta descriptions with category prefix, Open Graph, Twitter Cards, Schema.org DiscussionForumPosting, canonical management, related topics with images, reading time, author display, and more.
Version=2.1.1
Date=2026-07-04
Author=webitproff
Copyright=Copyright (c) 2025-2026 webitproff | https://github.com/webitproff
Notes=Requires the 'forums' module. Recommended plugins: attacher (for images), urleditor (for SEO URLs). Place the logo image for the default publisher logo in your theme or define a full URL in the configuration.
Auth_guests=R
Lock_guests=12345A
Auth_members=RW
Lock_members=
Requires_modules=forums
Recommends_modules=
Requires_plugins=
Recommends_plugins=attacher,urleditor
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
placeholderlogo=01:string:::Logo path (relative to main URL, e.g. themes/index36/img/logo.webp):themes/index36/img/logo.webp
placeholderimagedefault=02:string:::Default image path for topics without an image:/images/default.jpg
maxrelatedpostsperpage=03:select:0,1,2,3,5,7:3:Maximum related posts per page
[END_COT_EXT_CONFIG]
==================== */

/**
 * SEO Forum plugin – Setup and configuration
 * 
 * File:           plugins/seoforums/seoforums.setup.php
 
 * Purpose:        Register data in $db_core and $db_config. Setup & Config File for the Plugin. Registers the plugin in the system, provides metadata and  *                 configuration options. This plugin generates advanced SEO tags for forum pages,
 *                 adds related topics, reading time, author info, and manages canonical URLs.
 * 
 * Plugin:         SEO Forum for Cotonti CMF
 * Compatibility:  Cotonti v.1+, PHP 8.4+, MySQL 8.0+
 * Version:        2.1.1
 * Date:           2026-07-04
 * 
 * @package        seoforums
 * @author         webitproff
 * @copyright      Copyright (c) 2025-2026 webitproff | https://github.com/webitproff
 * @license        BSD
 * @link           https://github.com/webitproff/cot-seoforums
 */

defined('COT_CODE') or die('Wrong URL');

//$related_topics = $db->query("SELECT ft_id, ft_title, ft_desc, ft_image FROM $db_forum_topics WHERE ft_cat = ? AND ft_id != ? ORDER BY ft_updated DESC LIMIT 3", [$cat, $topic_id])->fetchAll();
//'RELATED_TOPIC_IMAGE' => !empty($rel['ft_image']) ? $rel['ft_image'] : Cot::$cfg['mainurl'] . Cot::$cfg['plugin']['seoforums']['placeholderimagedefault']
