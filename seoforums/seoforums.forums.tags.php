<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=forums.posts.tags
[END_COT_EXT]
==================== */

/**
 * SEO Forum: Определение тегов для страниц форума
 * Filename: seoforums.forums.tags.php
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

global $db, $db_forum_posts, $db_forum_topics, $db_users;

// Получаем ID темы
$topic_id = (int)cot_import('q', 'G', 'INT');
// file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Topic ID: $topic_id\n", FILE_APPEND);

if ($topic_id > 0) {
    // Данные темы
    $topic = $db->query("SELECT ft_id, ft_cat, ft_firstposterid FROM $db_forum_topics WHERE ft_id = ?", [$topic_id])->fetch();
    // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Topic exists: " . ($topic ? 'Yes' : 'No') . "\n", FILE_APPEND);

    if ($topic) {
        // Время чтения (первый пост)
        $first_post = $db->query("SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1", [$topic_id])->fetch();
        $text = $first_post['fp_text'] ?? '';
        $read_time = cot_estimate_read_time_forums($text);
        $t->assign([
            'TOPIC_READ_TIME' => $read_time . ' ' . (Cot::$L['seoforums_read_time'] ?? 'мин чтения')
        ]);

        // Автор темы
        $author_name = Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор';
        if ($topic['ft_firstposterid'] > 0) {
            $author_name = $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$topic['ft_firstposterid']])->fetchColumn() ?: Cot::$L['seoforums_unknown_author'];
        }
        $t->assign([
            'TOPIC_AUTHOR' => htmlspecialchars($author_name)
        ]);

        // Связанные темы
        $max_related = max((int)Cot::$cfg['plugin']['seoforums']['maxrelatedpostsperpage'], 1);
        // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Max related: $max_related\n", FILE_APPEND);
        $image_field = $db->query("SHOW COLUMNS FROM $db_forum_topics LIKE 'ft_image'")->rowCount() > 0 ? ', ft_image' : '';
        $related_topics = $db->query("SELECT ft_id, ft_title, ft_desc, ft_firstposterid$image_field FROM $db_forum_topics WHERE ft_cat = ? AND ft_id != ? ORDER BY ft_updated DESC LIMIT ?", [$topic['ft_cat'], $topic_id, $max_related])->fetchAll();
        // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Related topics (same cat): " . count($related_topics) . "\n", FILE_APPEND);

        // Если в категории нет тем, берём из других категорий
        if (empty($related_topics)) {
            $related_topics = $db->query("SELECT ft_id, ft_title, ft_desc, ft_firstposterid$image_field FROM $db_forum_topics WHERE ft_id != ? ORDER BY ft_updated DESC LIMIT ?", [$topic_id, $max_related])->fetchAll();
            // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Related topics (all cats): " . count($related_topics) . "\n", FILE_APPEND);
        }

        // Отладка: записываем количество тем и их структуру в лог
        // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Related topics count: " . count($related_topics) . "\n", FILE_APPEND);
        // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Related topics structure: " . print_r($related_topics, true) . "\n", FILE_APPEND);

        // Обрабатываем связанные темы для XTemplate
        foreach ($related_topics as $rel) {
            $rel_author_name = Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор';
            if ($rel['ft_firstposterid'] > 0) {
                $rel_author_name = $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$rel['ft_firstposterid']])->fetchColumn() ?: Cot::$L['seoforums_unknown_author'];
            }
            // Если ft_desc пустое, берём текст первого поста
            $desc = $rel['ft_desc'] ?? '';
            if (empty($desc)) {
                $rel_post = $db->query("SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1", [$rel['ft_id']])->fetch();
                $desc = strip_tags($rel_post['fp_text']) ?? '';
            }
			//$t->assign(cot_generate_usertags($rel, 'RELATED_TOPIC_ROW_USER_'));
            $t->assign([
                'RELATED_TOPIC_ROW_URL' => cot_url('forums', ['m' => 'posts', 'q' => $rel['ft_id']]),
                'RELATED_TOPIC_ROW_TITLE' => htmlspecialchars($rel['ft_title'] ?? ''),
                'RELATED_TOPIC_ROW_DESC' => cot_string_truncate(preg_replace('/[\'"`]+/', '', preg_replace('/\s+/', ' ', trim(strip_tags(html_entity_decode($desc))))), 100),
                'RELATED_TOPIC_ROW_IMAGE' => !empty($rel['ft_image']) ? $rel['ft_image'] : Cot::$cfg['mainurl'] . Cot::$cfg['plugin']['seoforums']['placeholderlogo'],
                'RELATED_TOPIC_ROW_AUTHOR' => htmlspecialchars($rel_author_name)
            ]);
            // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Parsing RELATED_ROW iteration: " . htmlspecialchars($rel['ft_title'] ?? '') . "\n", FILE_APPEND);
            $t->parse('MAIN.RELATED_TOPICS.RELATED_ROW');
        }
        if (!empty($related_topics)) {
            // file_put_contents('datas/debug.log', "[" . date('Y-m-d H:i:s') . "] Parsing MAIN.RELATED_TOPICS\n", FILE_APPEND);
            $t->parse('MAIN.RELATED_TOPICS');
        }
    }
}