<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=forums.posts.tags
[END_COT_EXT]
==================== */

/**
 * SEO Forum – вывод тегов для страниц форума (связанные темы, автор, время чтения)
 * 
 * Файл:          plugins/seoforums/seoforums.forums.posts.tags.php
 * Назначение:    Формирует и передаёт в шаблон данные для блока «Похожие темы»,
 *                а также теги автора темы и времени чтения первого поста.
 *                Используется в шаблонах постов (forums.posts.tpl и производных).
 * 
 * Плагин:        SEO Forum для Cotonti CMF
 * Совместимость: Cotonti v.1+, PHP 8.4+, MySQL 8.0+
 * Дата:          2026-07-04
 * Версия:        2.1.1
 * 
 * @package       seoforums
 * @author        webitproff
 * @copyright     Copyright (c) webitproff 2026 | https://github.com/webitproff
 * @license       BSD
 * @link          https://github.com/webitproff/cot-seoforums
 */

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('seoforums', 'plug');

global $db, $db_forum_posts, $db_forum_topics, $db_users, $db_structure;

// Получаем ID темы из URL
$topic_id = (int)cot_import('q', 'G', 'INT');

if ($topic_id > 0) {
    // Данные темы
    $topic = $db->query("SELECT ft_id, ft_cat, ft_firstposterid FROM $db_forum_topics WHERE ft_id = ?", [$topic_id])->fetch();

    if ($topic) {
        // --- Время чтения (первый пост) ---
        $first_post = $db->query("SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1", [$topic_id])->fetch();
        $text = $first_post['fp_text'] ?? '';
        $read_time = cot_estimate_read_time_forums($text);
        $t->assign([
            'TOPIC_READ_TIME' => $read_time . ' ' . (Cot::$L['seoforums_read_time'] ?? 'мин чтения')
        ]);

        // --- Автор темы ---
        $author_name = Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор';
        if ($topic['ft_firstposterid'] > 0) {
            $author_name = $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$topic['ft_firstposterid']])->fetchColumn() ?: (Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор');
        }
        $t->assign([
            'TOPIC_AUTHOR' => htmlspecialchars($author_name)
        ]);

        // --- Связанные темы ---
        $max_related = max((int)Cot::$cfg['plugin']['seoforums']['maxrelatedpostsperpage'], 1);
        $related_topics = $db->query(
            "SELECT ft_id, ft_title, ft_desc, ft_firstposterid, ft_cat FROM $db_forum_topics WHERE ft_cat = ? AND ft_id != ? ORDER BY ft_updated DESC LIMIT ?",
            [$topic['ft_cat'], $topic_id, $max_related]
        )->fetchAll();

        // Если в той же категории тем нет, берём из других
        if (empty($related_topics)) {
            $related_topics = $db->query(
                "SELECT ft_id, ft_title, ft_desc, ft_firstposterid, ft_cat FROM $db_forum_topics WHERE ft_id != ? ORDER BY ft_updated DESC LIMIT ?",
                [$topic_id, $max_related]
            )->fetchAll();
        }

        // Обрабатываем каждую связанную тему
        foreach ($related_topics as $rel) {
            // Автор связанной темы
            $rel_author_name = Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор';
            if ($rel['ft_firstposterid'] > 0) {
                $rel_author_name = $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$rel['ft_firstposterid']])->fetchColumn() ?: (Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор');
            }

            // Описание: сначала ft_desc, иначе текст первого поста
            $desc = $rel['ft_desc'] ?? '';
            if (empty($desc)) {
                $rel_post = $db->query("SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1", [$rel['ft_id']])->fetch();
                $desc = $rel_post['fp_text'] ?? '';
            }
            $page_desc = strip_tags(html_entity_decode($desc));
            $page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
            $page_desc = preg_replace('/\s+/', ' ', trim($page_desc));

            // Получаем название категории для связанной темы
            $category_code = $rel['ft_cat'] ?? '';
            $category_name = !empty($category_code)
                ? $db->query("SELECT structure_title FROM $db_structure WHERE structure_code = ? AND structure_area = 'forums'", [$category_code])->fetchColumn()
                : '';
            $category_name = !empty($category_name) ? htmlspecialchars($category_name) : htmlspecialchars($category_code);

            // Умная обрезка описания (как в meta description)
            $full_desc = $page_desc;
            $max_len = 160;
            if (mb_strlen($full_desc) > $max_len) {
                $cut_at = false;
                for ($i = $max_len; $i < min($max_len + 90, mb_strlen($full_desc)); $i++) {
                    $char = mb_substr($full_desc, $i, 1);
                    if ($char === '.' || $char === ',' || $char === '!' || $char === '?') {
                        $cut_at = $i;
                        break;
                    }
                }
                if ($cut_at !== false) {
                    $full_desc = mb_substr($full_desc, 0, $cut_at + 1);
                } else {
                    $space_pos = mb_strrpos(mb_substr($full_desc, 0, $max_len), ' ');
                    if ($space_pos !== false) {
                        $full_desc = mb_substr($full_desc, 0, $space_pos);
                    } else {
                        $full_desc = mb_substr($full_desc, 0, $max_len);
                    }
                }
            }
            $related_desc = $category_name . '. ' . $full_desc;

            $t->assign([
                'RELATED_TOPIC_ROW_URL'    => cot_url('forums', ['m' => 'posts', 'q' => $rel['ft_id']]),
                'RELATED_TOPIC_ROW_TITLE'  => htmlspecialchars($rel['ft_title'] ?? ''),
                'RELATED_TOPIC_ROW_DESC'   => $related_desc,
                'RELATED_TOPIC_ROW_IMAGE'  => seoforums_get_topic_image($rel['ft_id']),
                'RELATED_TOPIC_ROW_AUTHOR' => htmlspecialchars($rel_author_name)
            ]);

            $t->parse('MAIN.RELATED_TOPICS.RELATED_ROW');
        }

        if (!empty($related_topics)) {
            $t->parse('MAIN.RELATED_TOPICS');
        }
    }
}
