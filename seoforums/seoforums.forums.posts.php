<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=forums.posts.main
[END_COT_EXT]
==================== */


/**
 * SEO Forum: Обработка страницы темы форума и подготовка к выводу в шаблон
 * Filename: seoforums.forums.posts.php
 * Version=2.0.1
 * Date=2025-10-19
 * @package SeoForum for CMF Cotonti Siena v.0.9.26 on PHP 8.4
 * @author webitproff
 * @copyright Copyright (c) 2025 webitproff | https://github.com/webitproff
 * @license BSD License
 */
 
defined('COT_CODE') or die('Wrong URL');



require_once cot_incfile('seoforums', 'plug');
require_once cot_langfile('seoforums', 'plug');

global $db, $db_forum_topics, $db_forum_posts, $db_users, $db_structure;
$db_forum_topics = $db_x . 'forum_topics';
// Получаем ID темы из $_GET['q']
$topic_id = isset($_GET['q']) && is_numeric($_GET['q']) ? (int)$_GET['q'] : 0;

// Проверяем, что это валидная тема
if ($topic_id > 0) {
    // Получаем данные темы
	global $db_forum_topics;
    $topic = $db->query("SELECT ft_id, ft_title, ft_desc, ft_firstposterid, ft_creationdate, ft_updated, ft_cat FROM $db_forum_topics WHERE ft_id = ?", [$topic_id])->fetch();
    
    if ($topic) {
        // Изображение
        $seo_image = Cot::$cfg['mainurl'] . Cot::$cfg['plugin']['seoforums']['placeholderimagedefault'];
        // Локаль TODO edit
        $locale = isset(Cot::$cfg['locale']) ? Cot::$cfg['locale'] : 'ru_RU';
        // Автор
        $author_id = $topic['ft_firstposterid'] ?? 0;
        $author_name = ($author_id > 0) ? $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$author_id])->fetchColumn() : '';
        $author_name = !empty($author_name) ? htmlspecialchars($author_name) : (Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор');
		//$author_url = Cot::$cfg['mainurl'] . '/' . cot_url('users', ['m' => 'details', 'id' => $author_id], '', true);
		//$author_url = Cot::$cfg['mainurl'] . '/' . cot_url('users', ['m' => 'details', 'id' => $author_id]);
		$author_url = Cot::$cfg['mainurl'] . '/' . cot_url('users', ['m' => 'details', 'id' => $author_name]);
        // Заголовок
        $page_title = $topic['ft_title'] ?? '';
        // Описание
        $first_post = $db->query("SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1", [$topic_id])->fetch();
        $page_desc = !empty($topic['ft_desc']) ? $topic['ft_desc'] : strip_tags(html_entity_decode($first_post['fp_text'] ?? ''));
        $page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
        $page_desc = preg_replace('/\s+/', ' ', trim($page_desc));
        // Категория
        $category_code = $topic['ft_cat'] ?? '';
        $category_name = !empty($category_code) ? $db->query("SELECT structure_title FROM $db_structure WHERE structure_code = ? AND structure_area = 'forums'", [$category_code])->fetchColumn() : '';
        $category_name = !empty($category_name) ? htmlspecialchars($category_name) : htmlspecialchars($category_code);
        // Добавляем категорию в описание
        $meta_desc = cot_string_truncate($category_name . ': ' . $page_desc, 160);
        // Ключевые слова
        $page_keywords = cot_extract_keywords_forums($first_post['fp_text'] ?? '', 10);
        // Даты
        $page_date = $topic['ft_creationdate'] ?? time();
        $page_updated = $topic['ft_updated'] ?? time();
        // URL
        $page_url = Cot::$cfg['mainurl'] . '/' . cot_url('forums', ['m' => 'posts', 'q' => $topic_id]);

        // Мета-теги
        $meta_tags = '
            <!-- Open Graph -->
            <meta property="og:title" content="' . htmlspecialchars($page_title) . '">
            <meta property="og:description" content="' . htmlspecialchars($meta_desc) . '">
            <meta property="og:type" content="article">
            <meta property="og:url" content="' . htmlspecialchars($page_url) . '">
            <meta property="og:image" content="' . htmlspecialchars($seo_image) . '">
            <meta property="og:image:alt" content="' . htmlspecialchars($page_title) . '">
            <meta property="og:site_name" content="' . htmlspecialchars(Cot::$cfg['maintitle']) . '">
            <meta property="og:locale" content="' . htmlspecialchars($locale) . '">
            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="' . htmlspecialchars($page_title) . '">
            <meta name="twitter:description" content="' . htmlspecialchars($meta_desc) . '">
            <meta name="twitter:image" content="' . htmlspecialchars($seo_image) . '">
            <!-- Schema.org -->
            <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "DiscussionForumPosting",
              "headline": "' . htmlspecialchars($page_title) . '",
			  "url": "' . htmlspecialchars($page_url) . '",
              "description": "' . htmlspecialchars($meta_desc) . '",
              "keywords": "' . htmlspecialchars($page_keywords) . '",
              "articleSection": "' . $category_name . '",
              "author": {
                "@type": "Person",
                "name": "' . htmlspecialchars($author_name) . '",
                  "url": "' . str_replace('&amp;', '&', $author_url) . '"
              },
              "publisher": {
                "@type": "Organization",
                "name": "' . htmlspecialchars(Cot::$cfg['maintitle']) . '",
                "logo": {
                  "@type": "ImageObject",
                  "url": "' . Cot::$cfg['mainurl'] . Cot::$cfg['plugin']['seoforums']['placeholderlogo'] . '"
                }
              },
              "datePublished": "' . cot_date('c', $page_date) . '",
              "dateModified": "' . cot_date('c', $page_updated) . '",
              "image": "' . htmlspecialchars($seo_image) . '",
              "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "' . htmlspecialchars($page_url) . '"
              }
            }
            </script>';

        // Добавляем мета-теги
        global $out;
        $out['meta'] = (isset($out['meta']) ? $out['meta'] : '') . $meta_tags;
    }
}