<?php
/* ====================
[BEGIN_COT_EXT]
Code=seoforums
Name=SEO Forums
Category=content-seo
Description=SEO enhancements for the Forums module
Version=1.1.5
Date=2025-10-19
Author=webitproff
Copyright=Copyright (c) 2025 webitproff | https://github.com/webitproff
Notes=
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
placeholderlogo=01:string:::Logo path:/images/logo.png
placeholderimagedefault=02:string:::Default image path:/images/default.jpg
maxrelatedpostsperpage=03:select:0,1,2,3,5,7:3:Maximum related posts per page
[END_COT_EXT_CONFIG]
==================== */


/**
 * SEO Forum: Регистрация и конфигурация плагина
 * Filename: seoforums.setup.php
 * Version=2.0.1
 * Date=2025-10-19
 * @package SeoForum for CMF Cotonti Siena v.0.9.26 on PHP 8.4
 * @author webitproff
 * @copyright Copyright (c) 2025 webitproff | https://github.com/webitproff
 * @license BSD License
 */
 
defined('COT_CODE') or die('Wrong URL');

//$related_topics = $db->query("SELECT ft_id, ft_title, ft_desc, ft_image FROM $db_forum_topics WHERE ft_cat = ? AND ft_id != ? ORDER BY ft_updated DESC LIMIT 3", [$cat, $topic_id])->fetchAll();
//'RELATED_TOPIC_IMAGE' => !empty($rel['ft_image']) ? $rel['ft_image'] : Cot::$cfg['mainurl'] . Cot::$cfg['plugin']['seoforums']['placeholderimagedefault']