<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=forums.posts.main
[END_COT_EXT]
==================== */

/**
 * SEO Forum – обработка страниц постов форума
 * 
 * Файл:          plugins/seoforums/seoforums.forums.posts.php
 * Назначение:    Генерирует расширенные SEO-метатеги (Open Graph, Twitter Card,
 *                Schema.org DiscussionForumPosting) для списка постов темы,
 *                а также для одиночного поста (при активном forums_singlepost).
 *                Подменяет системный canonical на URL конкретного поста,
 *                переопределяет subtitle страницы.
 * 
 * Плагин:        SEO Forum для Cotonti CMF
 * Совместимость: Cotonti v.1+, PHP 8.4+, MySQL 8.0+
 * Дата:          2026-07-03
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

global $db, $db_forum_topics, $db_forum_posts, $db_users, $db_structure;
$db_forum_topics = $db_x . 'forum_topics';

// --- определение $topic_id и $post_id (общая часть) ---
$topic_id = isset($_GET['q']) && is_numeric($_GET['q']) ? (int)$_GET['q'] : 0;
$post_id  = 0;

if ($topic_id <= 0) {
    if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
        $post_id = (int)$_GET['id'];
    } elseif (!empty($_GET['p']) && is_numeric($_GET['p'])) {
        $post_id = (int)$_GET['p'];
    }
    if ($post_id > 0) {
        $topic_id = (int) $db->query(
            "SELECT fp_topicid FROM $db_forum_posts WHERE fp_id = ?",
            [$post_id]
        )->fetchColumn();
    }
}

// --- переопределяем subtitle (убираем лишнее) ---
global $rowt;
if (!empty($rowt['ft_title'])) {
    Cot::$out['subtitle'] = htmlspecialchars($rowt['ft_title']);
}

// ----------------------------------------------------------
// СЕКЦИЯ 1: Одиночный пост (есть $post_id и определена тема)
// ----------------------------------------------------------
if ($post_id > 0 && $topic_id > 0) {
    // Получаем данные темы (нужны для заголовка, дат, категории)
    $topic = $db->query(
        "SELECT ft_id, ft_title, ft_desc, ft_firstposterid, ft_creationdate, ft_updated, ft_cat
         FROM $db_forum_topics WHERE ft_id = ?",
        [$topic_id]
    )->fetch();

    // Получаем данные самого поста
    $single_post = $db->query(
        "SELECT fp_text, fp_posterid, fp_postername FROM $db_forum_posts WHERE fp_id = ?",
        [$post_id]
    )->fetch();

    if ($topic && $single_post) {
        // Текст поста
        $single_post_text = $single_post['fp_text'];
        // Очищенное описание (для meta description)
        $page_desc = strip_tags(html_entity_decode($single_post_text));
        $page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
        $page_desc = preg_replace('/\s+/', ' ', trim($page_desc));
        // Ключевые слова из текста поста
        $page_keywords = cot_extract_keywords_forums($single_post_text, 10);

        // Длинное описание для JSON-LD (до 2000 символов)
        $json_raw = strip_tags(html_entity_decode($single_post_text));
        $json_raw = trim(preg_replace('/\s+/', ' ', $json_raw));
        if (mb_strlen($json_raw) > 2000) {
            $json_raw = mb_substr($json_raw, 0, 2000) . '…';
        }
        $json_description = $json_raw;

        // Автор поста
        $author_id = (int)$single_post['fp_posterid'];
        if ($author_id > 0) {
            $author_name_db = $db->query(
                "SELECT user_name FROM $db_users WHERE user_id = ?",
                [$author_id]
            )->fetchColumn();
            $author_name = !empty($author_name_db) ? $author_name_db : $single_post['fp_postername'];
        } else {
            $author_name = $single_post['fp_postername'] ?: (Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор');
        }
        $author_name = htmlspecialchars($author_name);
        $author_url = Cot::$cfg['mainurl'] . '/' . cot_url('users', ['m' => 'details', 'id' => $author_name]);

        // Изображение (сначала пытаемся из поста, затем из темы)
        $seo_image = seoforums_get_post_image($post_id);
        if ($seo_image === rtrim(Cot::$cfg['mainurl'], '/') . '/' . ltrim(Cot::$cfg['plugin']['seoforums']['placeholderimagedefault'], '/')) {
            $seo_image = seoforums_get_topic_image($topic_id);
        }

        // Общие данные из темы
        $page_title = $topic['ft_title'] ?? '';
        //$locale = Cot::$cfg['locale'] ?? 'ru_RU';
		
		// --------------- НЕ ТРОГАТЬ!!! -------------------
		// Собираем локаль для Open Graph, соответствующую языку пользователя + регион UA
		//$langCode = !empty(Cot::$usr['lang']) ? Cot::$usr['lang'] : (Cot::$cfg['defaultlang'] ?? 'ru');
		// Стандартизируем код языка для Open Graph (ua -> uk)
		//$map = ['ua' => 'uk', 'ru' => 'ru', 'en' => 'en', 'pl' => 'pl']; // дополните при необходимости
		//$ogLang = $map[$langCode] ?? $langCode;
		//$locale = $ogLang . '_UA';   // например, ru_UA, uk_UA, en_UA
		// --------------- НЕ ТРОГАТЬ!!! -------------------
		
		// Определяем короткий код текущего языка (как в i18n)
		$shortLocale = $i18n_locale ?? Cot::$usr['lang'] ?? Cot::$cfg['defaultlang'] ?? 'ru';
		// Таблица соответствия коротких кодов полным IETF (как в i18n.header.tags.php)
		$i18n_ietf_map = [
			'ua' => 'uk-UA',
			'ru' => 'ru-UA',
			'en' => 'en-UA',
			'pl' => 'pl-UA'
		];
		// Преобразуем в полный тег и заменяем дефис на подчёркивание для og:locale
		$locale = isset($i18n_ietf_map[$shortLocale]) 
			? str_replace('-', '_', $i18n_ietf_map[$shortLocale]) 
			: $shortLocale . '_UA';
			
        $category_code = $topic['ft_cat'] ?? '';
        $category_name = !empty($category_code)
            ? $db->query("SELECT structure_title FROM $db_structure WHERE structure_code = ? AND structure_area = 'forums'", [$category_code])->fetchColumn()
            : '';
        $category_name = !empty($category_name) ? htmlspecialchars($category_name) : htmlspecialchars($category_code);
        // $meta_desc = cot_string_truncate($category_name . ': ' . $page_desc, 160);
		// Умная обрезка: ищем точку / запятую после 160 символов,
		// если нет — обрезаем по последнему пробелу перед 160 (слово не разрываем)
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
		$meta_desc = $category_name . '. ' . $full_desc;
        $page_date = $topic['ft_creationdate'] ?? time();
        $page_updated = $topic['ft_updated'] ?? time();

        // URL в зависимости от наличия forums_singlepost
        if (cot_plugin_active('forums_singlepost')) {
            $page_url = Cot::$cfg['mainurl'] . '/' . cot_url('forums', ['m' => 'posts', 'id' => $post_id]);
        } else {
            $page_url = Cot::$cfg['mainurl'] . '/' . cot_url('forums', ['m' => 'posts', 'q' => $topic_id]);
        }
        Cot::$out['canonical_uri'] = $page_url;

        // Собираем метатеги
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
          "headline": ' . json_encode($page_title, JSON_UNESCAPED_UNICODE) . ',
          "url": ' . json_encode($page_url, JSON_UNESCAPED_UNICODE) . ',
          "description": ' . json_encode($json_description, JSON_UNESCAPED_UNICODE) . ',
          "keywords": ' . json_encode($page_keywords, JSON_UNESCAPED_UNICODE) . ',
          "articleSection": ' . json_encode($category_name, JSON_UNESCAPED_UNICODE) . ',
          "author": {
            "@type": "Person",
            "name": ' . json_encode($author_name, JSON_UNESCAPED_UNICODE) . ',
            "url": ' . json_encode(str_replace('&amp;', '&', $author_url), JSON_UNESCAPED_UNICODE) . '
          },
          "publisher": {
            "@type": "Organization",
            "name": ' . json_encode(Cot::$cfg['maintitle'], JSON_UNESCAPED_UNICODE) . ',
            "logo": {
              "@type": "ImageObject",
              "url": ' . json_encode(Cot::$cfg['mainurl'] . Cot::$cfg['plugin']['seoforums']['placeholderlogo'], JSON_UNESCAPED_UNICODE) . '
            }
          },
          "datePublished": ' . json_encode(cot_date('c', $page_date), JSON_UNESCAPED_UNICODE) . ',
          "dateModified": ' . json_encode(cot_date('c', $page_updated), JSON_UNESCAPED_UNICODE) . ',
          "image": ' . json_encode($seo_image, JSON_UNESCAPED_UNICODE) . ',
          "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": ' . json_encode($page_url, JSON_UNESCAPED_UNICODE) . '
          }
        }
        </script>';

        global $out;
        $out['meta'] = (isset($out['meta']) ? $out['meta'] : '') . $meta_tags;
    }
}

// ----------------------------------------------------------
// СЕКЦИЯ 2: Список постов темы (нет одиночного поста)
// ----------------------------------------------------------
if ($post_id == 0 && $topic_id > 0) {
    $topic = $db->query(
        "SELECT ft_id, ft_title, ft_desc, ft_firstposterid, ft_creationdate, ft_updated, ft_cat
         FROM $db_forum_topics WHERE ft_id = ?",
        [$topic_id]
    )->fetch();

    if ($topic) {
        // Текст первого поста (для описания)
        $first_post_text = $db->query(
            "SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1",
            [$topic_id]
        )->fetchColumn();
        $page_desc = !empty($topic['ft_desc'])
            ? $topic['ft_desc']
            : strip_tags(html_entity_decode($first_post_text ?? ''));
        $page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
        $page_desc = preg_replace('/\s+/', ' ', trim($page_desc));
        $page_keywords = cot_extract_keywords_forums($first_post_text ?? '', 10);
        //$json_description = $page_desc;   // короткое описание для JSON-LD
		// Длинное описание для JSON-LD (до 2000 символов)
		$json_raw = strip_tags(html_entity_decode($first_post_text ?? ''));
		$json_raw = trim(preg_replace('/\s+/', ' ', $json_raw));
		if (mb_strlen($json_raw) > 2000) {
			$json_raw = mb_substr($json_raw, 0, 2000) . '…';
		}
		$json_description = $json_raw;
        // Автор первого поста
        $author_id = $topic['ft_firstposterid'] ?? 0;
        if ($author_id > 0) {
            $author_name_db = $db->query("SELECT user_name FROM $db_users WHERE user_id = ?", [$author_id])->fetchColumn();
            $author_name = !empty($author_name_db) ? $author_name_db : (Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор');
        } else {
            $author_name = Cot::$L['seoforums_unknown_author'] ?? 'Неизвестный автор';
        }
        $author_name = htmlspecialchars($author_name);
        $author_url = Cot::$cfg['mainurl'] . '/' . cot_url('users', ['m' => 'details', 'id' => $author_name]);

        $seo_image = seoforums_get_topic_image($topic_id);
        $page_title = $topic['ft_title'] ?? '';
		// --------------- НЕ ТРОГАТЬ!!! -------------------
		// Собираем локаль для Open Graph, соответствующую языку пользователя + регион UA
		//$langCode = !empty(Cot::$usr['lang']) ? Cot::$usr['lang'] : (Cot::$cfg['defaultlang'] ?? 'ru');
		// Стандартизируем код языка для Open Graph (ua -> uk)
		//$map = ['ua' => 'uk', 'ru' => 'ru', 'en' => 'en', 'pl' => 'pl']; // дополните при необходимости
		//$ogLang = $map[$langCode] ?? $langCode;
		//$locale = $ogLang . '_UA';   // например, ru_UA, uk_UA, en_UA
		// --------------- НЕ ТРОГАТЬ!!! -------------------
		
		// Определяем короткий код текущего языка (как в i18n)
		$shortLocale = $i18n_locale ?? Cot::$usr['lang'] ?? Cot::$cfg['defaultlang'] ?? 'ru';
		// Таблица соответствия коротких кодов полным IETF (как в i18n.header.tags.php)
		$i18n_ietf_map = [
			'ua' => 'uk-UA',
			'ru' => 'ru-UA',
			'en' => 'en-UA',
			'pl' => 'pl-UA'
		];
		// Преобразуем в полный тег и заменяем дефис на подчёркивание для og:locale
		$locale = isset($i18n_ietf_map[$shortLocale]) 
			? str_replace('-', '_', $i18n_ietf_map[$shortLocale]) 
			: $shortLocale . '_UA';
			
        $category_code = $topic['ft_cat'] ?? '';
        $category_name = !empty($category_code)
            ? $db->query("SELECT structure_title FROM $db_structure WHERE structure_code = ? AND structure_area = 'forums'", [$category_code])->fetchColumn()
            : '';
        $category_name = !empty($category_name) ? htmlspecialchars($category_name) : htmlspecialchars($category_code);
        // $meta_desc = cot_string_truncate($category_name . ': ' . $page_desc, 160);
		// Умная обрезка: ищем точку / запятую после 160 символов,
		// если нет — обрезаем по последнему пробелу перед 160 (слово не разрываем)
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
		$meta_desc = $category_name . '. ' . $full_desc;

        $page_date = $topic['ft_creationdate'] ?? time();
        $page_updated = $topic['ft_updated'] ?? time();
        $page_url = Cot::$cfg['mainurl'] . '/' . cot_url('forums', ['m' => 'posts', 'q' => $topic_id]);

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
          "headline": ' . json_encode($page_title, JSON_UNESCAPED_UNICODE) . ',
          "url": ' . json_encode($page_url, JSON_UNESCAPED_UNICODE) . ',
          "description": ' . json_encode($json_description, JSON_UNESCAPED_UNICODE) . ',
          "keywords": ' . json_encode($page_keywords, JSON_UNESCAPED_UNICODE) . ',
          "articleSection": ' . json_encode($category_name, JSON_UNESCAPED_UNICODE) . ',
          "author": {
            "@type": "Person",
            "name": ' . json_encode($author_name, JSON_UNESCAPED_UNICODE) . ',
            "url": ' . json_encode(str_replace('&amp;', '&', $author_url), JSON_UNESCAPED_UNICODE) . '
          },
          "publisher": {
            "@type": "Organization",
            "name": ' . json_encode(Cot::$cfg['maintitle'], JSON_UNESCAPED_UNICODE) . ',
            "logo": {
              "@type": "ImageObject",
              "url": ' . json_encode(Cot::$cfg['mainurl'] . Cot::$cfg['plugin']['seoforums']['placeholderlogo'], JSON_UNESCAPED_UNICODE) . '
            }
          },
          "datePublished": ' . json_encode(cot_date('c', $page_date), JSON_UNESCAPED_UNICODE) . ',
          "dateModified": ' . json_encode(cot_date('c', $page_updated), JSON_UNESCAPED_UNICODE) . ',
          "image": ' . json_encode($seo_image, JSON_UNESCAPED_UNICODE) . ',
          "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": ' . json_encode($page_url, JSON_UNESCAPED_UNICODE) . '
          }
        }
        </script>';

        global $out;
        $out['meta'] = (isset($out['meta']) ? $out['meta'] : '') . $meta_tags;
    }
}
