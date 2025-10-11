<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=header.tags
[END_COT_EXT]
==================== */

/**
 * SEO Forum: Переопределение HEADER_META_DESCRIPTION и HEADER_META_KEYWORDS
 * Filename: seoforums.header.tags.php
 * Version=2.0.1
 * Date=2025-10-19
 * @package SeoForum for CMF Cotonti Siena v.0.9.26 on PHP 8.4
 * @author webitproff
 * @copyright Copyright (c) 2025 webitproff | https://github.com/webitproff
 * @license BSD License
 */
defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('seoforums', 'plug');

// Проверяем, что это страница темы форума
if (isset($_GET['m']) && $_GET['m'] == 'posts' && isset($_GET['q']) && is_numeric($_GET['q'])) {
    global $db, $db_forum_topics, $db_forum_posts, $db_structure;
    $topic_id = (int)$_GET['q'];

    // Получаем данные темы
    $topic = $db->query("SELECT ft_title, ft_desc, ft_cat FROM $db_forum_topics WHERE ft_id = ?", [$topic_id])->fetch();
    
    if ($topic) {
        // Получаем название категории
        $category_name = $db->query("SELECT structure_title FROM $db_structure WHERE structure_code = ? AND structure_area = 'forums'", [$topic['ft_cat']])->fetchColumn();
        $category_name = !empty($category_name) ? htmlspecialchars($category_name) : htmlspecialchars($topic['ft_cat']);

        // Получаем текст первого поста
        $first_post = $db->query("SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1", [$topic_id])->fetch();
        // Описание: ft_desc или текст первого поста, плюс категория
        $page_desc = !empty($topic['ft_desc']) ? $topic['ft_desc'] : strip_tags(html_entity_decode($first_post['fp_text'] ?? ''));
        $page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
        $page_desc = preg_replace('/\s+/', ' ', trim($page_desc));
        $page_desc = cot_string_truncate($category_name . ': ' . $page_desc, 160);
        // Ключевые слова
        $page_keywords = cot_extract_keywords_forums($first_post['fp_text'] ?? '', 10);
    } else {
        $page_desc = '';
        $page_keywords = '';
    }
} else {
    // Для нефорумных страниц оставляем пустые значения
    $page_desc = '';
    $page_keywords = '';
}
if ($env['location'] === 'forums' && ($m ?? 'main') === 'main') {

    // Присваиваем мета-теги в шаблон header.tpl через объект шаблонизатора $t (Smarty).
    // HEADER_META_DESCRIPTION и HEADER_META_KEYWORDS используются в header.tpl, например:
    // <meta name="description" content="{HEADER_META_DESCRIPTION}">
    // <meta name="keywords" content="{HEADER_META_KEYWORDS}">
    $t->assign([
        'HEADER_META_DESCRIPTION' => $page_desc,
        'HEADER_META_KEYWORDS' => $page_keywords
    ]);
}

// Переопределяем HEADER_META_DESCRIPTION и HEADER_META_KEYWORDS
/* $t->assign([
    'HEADER_META_DESCRIPTION' => $page_desc,
    'HEADER_META_KEYWORDS' => $page_keywords
]); */