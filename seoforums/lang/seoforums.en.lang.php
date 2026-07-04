<?php
/**
 * SEO Forum plugin – English localization
 *
 * File:           plugins/seoforums/lang/seoforums.en.lang.php
 * Purpose:        Provides English translations for plugin configuration descriptions,
 *                UI strings, and default meta text for empty sections or authors.
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

// === Configuration labels ===
$L['cfg_placeholderlogo'] = 'Logo (small image)';
$L['cfg_placeholderlogo_hint'] = 'Relative path to the image, e.g. <code>themes/index36/img/logo.webp</code>. Used in publisher microdata.';
$L['cfg_placeholderimagedefault'] = 'Default topic image';
$L['cfg_placeholderimagedefault_hint'] = 'Relative path to the image, e.g. <code>/datas/images/static/logo-big.webp</code>. Shown when a topic has no image of its own.';
$L['cfg_maxrelatedpostsperpage'] = 'Max. related topics';
$L['cfg_maxrelatedpostsperpage_hint'] = 'Number of entries displayed in the <code>RELATED_TOPICS</code> block on a topic page. Set to 0 to disable.';

// === Plugin description ===
$L['info_name'] = 'SEO Forum & microdata';
$L['info_desc'] = 'Advanced SEO tools for the Forums module: smart meta descriptions, Open Graph, Twitter Cards, Schema.org DiscussionForumPosting, canonical management, related topics with images, reading time, author info, and more.';
$L['info_notes'] = 'Requires the forums module. Recommended plugins: attacher (for images) and urleditor (for SEO-friendly URLs). Tested with Cotonti v.1+ PHP 8.4+.';

// === Default texts for empty meta (fallback) ===
$L['seoforums_topics_empty_meta_title'] = 'Topics list';
$L['seoforums_topics_empty_meta_description'] = 'Browse all topics in this forum section.';
$L['seoforums_main_empty_meta_title'] = 'FunSmart Market Forums';
$L['seoforums_main_empty_meta_description'] = 'Community forums';
$L['seoforums_sections_empty_meta_title'] = 'Forum section';
$L['seoforums_sections_empty_meta_description'] = 'No description available for this section.';

// === Frontend strings ===
$L['seoforums_read_time'] = 'min read';
$L['seoforums_unknown_author'] = 'Unknown author';
$L['seoforums_related'] = 'Related topics';

// === Stop words for keyword extraction ===

$L['seoforums_stop_words'] = 'a,without,more,was,were,be,in,you,all,here,there,everyone,where,yes,even,for,until,his,her,if,is,still,then,here,and,from,or,them,their,to,when,who,whether,me,maybe,we,need,our,not,him,her,no,none,but,well,about,however,he,she,they,it,from,before,after,then,because,almost,at,about,again,what,whose,that,so,quite,like,there,those,now,that,too,only,you,already,although,what,whose,than,what,to,whose,whose,this,these,this,I';
