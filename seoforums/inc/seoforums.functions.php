<?php

/**
 * SEO Forum plugin – Helper functions
 * 
 * File:           plugins/seoforums/seoforums.functions.php
 * Purpose:        Provides core functions for keyword extraction, reading time estimation,
 *                and retrieving images for forum topics and single posts.
 *                These functions are used across the plugin (hooks, tags, etc.).
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
require_once cot_langfile('seoforums', 'plug');
/**
 * Extracts keywords from text for forum topics
 * @param string $text Input text
 * @param int $limit Maximum number of keywords to return
 * @return string Comma-separated keywords
 */
function cot_extract_keywords_forums($text, $limit = 10)
{
    if (empty($text)) {
        return '';
    }

    // Load stop words from language file
    $stop_words = !empty(Cot::$L['seoforums_stop_words']) ? explode(',', Cot::$L['seoforums_stop_words']) : [];
    $stop_words = array_map('trim', $stop_words);
    $stop_words = array_map('mb_strtolower', $stop_words);

    // Clean text: remove HTML, decode entities, convert to lowercase
    $text = strip_tags(html_entity_decode($text));
    $text = mb_strtolower($text, 'UTF-8');

    // Replace punctuation with spaces and normalize spaces
    $text = preg_replace('/[.,!?;:"\'\(\)\[\]{}<>\n\r\t]/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', trim($text));

    // Split into words
    $words = explode(' ', $text);
    $word_count = [];

    // Count word frequencies
    foreach ($words as $word) {
        $word = trim($word);
        if (mb_strlen($word) > 2 && !in_array($word, $stop_words)) {
            $word_count[$word] = isset($word_count[$word]) ? $word_count[$word] + 1 : 1;
        }
    }

    // Sort by frequency and limit
    arsort($word_count);
    $keywords = array_keys(array_slice($word_count, 0, $limit, true));

    // Return comma-separated keywords
    return implode(', ', $keywords);
}

/**
 * Estimates reading time for text in forum topics
 * @param string $text Input text
 * @return int Estimated reading time in minutes
 */
function cot_estimate_read_time_forums($text)
{
    if (empty($text)) {
        return 1; // Если текст пуст, возвращаем 1 минуту
    }

    // Удаляем HTML-теги и BB-коды
    $text = strip_tags($text);
    $text = preg_replace('/\[.*?\]/', '', $text);
    $text = trim($text);

    if (empty($text)) {
        return 1; // Если после очистки текст пуст, возвращаем 1 минуту
    }

    // Подсчитываем слова (для UTF-8, включая русский текст)
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $word_count = count($words);

    // Средняя скорость чтения: 200 слов в минуту
    $minutes = ceil($word_count / 200);

    return max(1, $minutes); // Минимальное время — 1 минута
}


function seoforums_get_topic_image(int $topic_id): string
{
    global $db, $db_forum_posts, $db_x;

    $default = rtrim(Cot::$cfg['mainurl'], '/') . '/' .
        ltrim(Cot::$cfg['plugin']['seoforums']['placeholderimagedefault'], '/');

    if ($topic_id <= 0) {
        return $default;
    }

    // Первый пост темы
    $first_post = $db->query(
        "SELECT fp_id FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1",
        [$topic_id]
    )->fetch();

    if (!$first_post || empty($first_post['fp_id'])) {
        return $default;
    }

    // Ищем вложение-изображение через attacher
    if (cot_plugin_active('attacher')) {
        global $db_attacher;
        if (empty($db_attacher)) {
            $db_attacher = $db_x . 'attacher';
        }

        $att = $db->query(
            "SELECT att_path
               FROM $db_attacher
              WHERE att_area = 'forums'
                AND att_item = ?
                AND att_img = 1
              ORDER BY att_order ASC
              LIMIT 1",
            [(int)$first_post['fp_id']]
        )->fetch();

        if ($att && !empty($att['att_path'])) {
            return rtrim(Cot::$cfg['mainurl'], '/') . '/' . ltrim($att['att_path'], '/');
        }
    }

    return $default;
}
/**
 * Returns the URL of the first image attached to a specific forum post.
 * Falls back to the default placeholder if no image is found.
 */
function seoforums_get_post_image(int $post_id): string
{
    global $db, $db_x;
    $default = rtrim(Cot::$cfg['mainurl'], '/') . '/' . ltrim(Cot::$cfg['plugin']['seoforums']['placeholderimagedefault'], '/');

    if ($post_id <= 0) {
        return $default;
    }

    if (cot_plugin_active('attacher')) {
        global $db_attacher;
        if (empty($db_attacher)) {
            $db_attacher = $db_x . 'attacher';
        }

        $att = $db->query(
            "SELECT att_path FROM $db_attacher
              WHERE att_area = 'forums'
                AND att_item = ?
                AND att_img = 1
              ORDER BY att_order ASC
              LIMIT 1",
            [$post_id]
        )->fetch();

        if ($att && !empty($att['att_path'])) {
            return rtrim(Cot::$cfg['mainurl'], '/') . '/' . ltrim($att['att_path'], '/');
        }
    }

    return $default;
}
