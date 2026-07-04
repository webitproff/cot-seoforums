<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=header.tags
[END_COT_EXT]
==================== */

/**
 * SEO Forum – переопределение мета-тегов в <head> страниц форума
 * 
 * Файл:          plugins/seoforums/seoforums.header.tags.php
 * Назначение:    Устанавливает кастомные заголовки (<title>), мета-описания,
 *                ключевые слова и канонические URL для всех страниц модуля forums:
 *                список постов, одиночный пост, список тем, главная форума,
 *                разделы (категории).  Работает через хук header.tags.
 * 
 * Плагин:        SEO Forum для Cotonti CMF
 * Совместимость: Cotonti v.0.9.26+, PHP 8.4+, MySQL 8.0+
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


// ----------------------------------------------------------
// СЕКЦИЯ 1: Список постов (m=posts) + одиночный пост
// ----------------------------------------------------------
// Проверяем, что GET-параметр 'm' существует и равен 'posts', то есть мы на странице постов темы
if (isset($_GET['m']) && $_GET['m'] == 'posts') {
    // Инициализируем ID темы и ID поста нулями (если они не будут получены из GET)
    $topic_id = 0;
    $post_id  = 0;

    // Пытаемся получить ID темы из параметра 'q' (присутствует при просмотре всей темы)
    if (!empty($_GET['q']) && is_numeric($_GET['q'])) {
        $topic_id = (int)$_GET['q'];
    }
    // Пытаемся получить ID конкретного поста из параметра 'id' (прямая ссылка на пост)
    if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
        $post_id = (int)$_GET['id'];
    // Если 'id' не передан, пытаемся получить ID поста из параметра 'p' (альтернативный формат ссылки)
    } elseif (!empty($_GET['p']) && is_numeric($_GET['p'])) {
        $post_id = (int)$_GET['p'];
    }
    // Если ID темы по-прежнему не определён, но есть ID поста, определяем тему через запрос к БД
    if ($topic_id <= 0 && $post_id > 0) {
        // Объявляем глобальные переменные БД, чтобы использовать их внутри хука
        global $db, $db_forum_posts;
        // Получаем ID темы, к которой принадлежит заданный пост
        $topic_id = (int) $db->query(
            "SELECT fp_topicid FROM $db_forum_posts WHERE fp_id = ?",
            [$post_id]
        )->fetchColumn();
    }

    // Инициализируем описание и ключевые слова как пустые строки
    $page_desc = '';
    $page_keywords = '';
    // Если ID темы удалось определить, формируем мета-теги
    if ($topic_id > 0) {
        // Объявляем глобальные переменные для работы с БД (таблицы форумов и структуры)
        global $db, $db_forum_topics, $db_forum_posts, $db_structure;

        // Получаем информацию о теме: заголовок, описание, категорию
        $topic = $db->query(
            "SELECT ft_title, ft_desc, ft_cat FROM $db_forum_topics WHERE ft_id = ?",
            [$topic_id]
        )->fetch();

        // Если тема существует, продолжаем
        if ($topic) {
            // Получаем название категории (раздела) из структуры форумов
            $category_name = $db->query(
                "SELECT structure_title FROM $db_structure WHERE structure_code = ? AND structure_area = 'forums'",
                [$topic['ft_cat']]
            )->fetchColumn();
            // Если название категории не найдено, используем код категории, иначе экранируем найденное
            $category_name = !empty($category_name) ? htmlspecialchars($category_name) : htmlspecialchars($topic['ft_cat']);

            // Если просматривается одиночный пост
            if ($post_id > 0) {
                // Получаем текст этого конкретного поста
                $post_text = $db->query(
                    "SELECT fp_text FROM $db_forum_posts WHERE fp_id = ?",
                    [$post_id]
                )->fetchColumn();
                // Формируем описание: очищаем от тегов, декодируем HTML-сущности
                $page_desc = strip_tags(html_entity_decode($post_text ?? ''));
                // Генерируем ключевые слова из текста поста (максимум 10 слов)
                $page_keywords = cot_extract_keywords_forums($post_text ?? '', 10);
            } else {
                // Если просматривается вся тема, получаем первый пост
                $first_post = $db->query(
                    "SELECT fp_text FROM $db_forum_posts WHERE fp_topicid = ? ORDER BY fp_id ASC LIMIT 1",
                    [$topic_id]
                )->fetch();
                // Формируем описание: используем ft_desc темы, если оно есть, иначе текст первого поста
                $page_desc = !empty($topic['ft_desc'])
                    ? $topic['ft_desc']
                    : strip_tags(html_entity_decode($first_post['fp_text'] ?? ''));
                // Генерируем ключевые слова из текста первого поста
                $page_keywords = cot_extract_keywords_forums($first_post['fp_text'] ?? '', 10);
            }
            // Убираем одиночные/двойные кавычки и обратные апострофы из описания
            $page_desc = preg_replace('/[\'"`]+/', '', $page_desc);
            // Заменяем множественные пробелы на один и обрезаем края
            $page_desc = preg_replace('/\s+/', ' ', trim($page_desc));
            // Обрезаем описание до 160 символов, добавляя название категории в начало
            // $page_desc = cot_string_truncate($category_name . ': ' . $page_desc, 160);
            // Умная обрезка: отсекаем по ближайшей точке/запятой после 160 символов
            // Умная обрезка: ищем точку / запятую после 160 символов,
            // если нет — обрезаем по последнему пробелу перед 160 (слово не разрываем)
            $full_desc = $page_desc;
            $max_len = 160;
            if (mb_strlen($full_desc) > $max_len) {
                // Ищем точку или запятую начиная с позиции 160 и до 250
                $cut_at = false;
                for ($i = $max_len; $i < min($max_len + 90, mb_strlen($full_desc)); $i++) {
                    $char = mb_substr($full_desc, $i, 1);
                    if ($char === '.' || $char === ',' || $char === '!' || $char === '?') {
                        $cut_at = $i;
                        break;
                    }
                }
                if ($cut_at !== false) {
                    // Нашли знак — обрезаем до него включительно
                    $full_desc = mb_substr($full_desc, 0, $cut_at + 1);
                } else {
                    // Знак не найден — обрезаем по последнему пробелу перед 160
                    $space_pos = mb_strrpos(mb_substr($full_desc, 0, $max_len), ' ');
                    if ($space_pos !== false) {
                        $full_desc = mb_substr($full_desc, 0, $space_pos);
                    } else {
                        // Если нет пробелов, оставляем как есть (не обрезаем)
                        $full_desc = mb_substr($full_desc, 0, $max_len);
                    }
                }
            }
            // Название категории + точка + пробел + обработанное описание
            $page_desc = $category_name . '. ' . $full_desc;
        }
    }

    // Присваиваем сформированные мета-описание и ключевые слова в шаблон
    $t->assign([
        'HEADER_META_DESCRIPTION' => $page_desc,
        'HEADER_META_KEYWORDS'    => $page_keywords
    ]);

    // Если это одиночный пост и активирован плагин forums_singlepost, переопределяем канонический URL
    if ($post_id > 0 && cot_plugin_active('forums_singlepost')) {
        // Формируем абсолютный URL на конкретный пост (а не на тему)
        $canonical = Cot::$cfg['mainurl'] . '/' . cot_url('forums', ['m' => 'posts', 'id' => $post_id]);
        // Присваиваем его в шаблон
        $t->assign('HEADER_CANONICAL_URL', $canonical);
    }
}
// ----------------------------------------------------------
// СЕКЦИЯ 2: Список тем (m=topics)
// ----------------------------------------------------------
if (isset($_GET['m']) && $_GET['m'] == 'topics' && !empty($_GET['s'])) {
    // $s — это код раздела (structure_code) из URL, например 'cat-code-alias'
    $s = $_GET['s'];
    // Проверяем, существует ли такой раздел в структуре форумов
    if (isset(Cot::$structure['forums'][$s])) {

        // === ПЕРЕОПРЕДЕЛЕНИЕ ЗАГОЛОВКА (TITLE) ===
        // Пытаемся взять индивидуальный мета-заголовок раздела из настроек
        if (!empty(Cot::$cfg['forums']['cat_' . $s]['metatitle'])) {
            $title = Cot::$cfg['forums']['cat_' . $s]['metatitle'];
        // Если мета-заголовок не задан, берём название раздела из структуры
        } elseif (!empty(Cot::$structure['forums'][$s]['title'])) {
            $title = Cot::$structure['forums'][$s]['title'];
        // Если нет ни того, ни другого — используем дефолтную языковую строку
        } else {
            $title = Cot::$L['seoforums_topics_empty_meta_title'];
        }
        // Экранируем заголовок и присваиваем его в шаблон
        $t->assign('HEADER_TITLE', htmlspecialchars($title));

        // === ПЕРЕОПРЕДЕЛЕНИЕ META DESCRIPTION ===
        // Пытаемся взять индивидуальное мета-описание раздела из настроек
        if (!empty(Cot::$cfg['forums']['cat_' . $s]['metadesc'])) {
            $desc = Cot::$cfg['forums']['cat_' . $s]['metadesc'];
        // Если мета-описание не задано, берём описание раздела из структуры
        } elseif (!empty(Cot::$structure['forums'][$s]['desc'])) {
            $desc = Cot::$structure['forums'][$s]['desc'];
        // Если нет ни того, ни другого — используем дефолтную языковую строку
        } else {
            $desc = Cot::$L['seoforums_topics_empty_meta_description'];
        }
        // Очищаем описание от HTML-тегов и преобразуем спецсимволы
        $desc = htmlspecialchars(strip_tags($desc));
        // Присваиваем мета-описание в шаблон
        $t->assign('HEADER_META_DESCRIPTION', $desc);

        // === КЛЮЧЕВЫЕ СЛОВА ===
        // Если для раздела заданы ключевые слова в настройках, присваиваем их
        if (!empty(Cot::$cfg['forums']['cat_' . $s]['keywords'])) {
            $t->assign('HEADER_META_KEYWORDS', Cot::$cfg['forums']['cat_' . $s]['keywords']);
        }

        // === CANONICAL URL ===
        // Формируем абсолютный канонический URL для страницы списка тем этого раздела
        $canonical = Cot::$cfg['mainurl'] . '/' . cot_url('forums', ['m' => 'topics', 's' => $s]);
        $t->assign('HEADER_CANONICAL_URL', $canonical);
    }
}
// ----------------------------------------------------------
// СЕКЦИЯ 3a: Главная страница форума (без выбранной категории)
// ----------------------------------------------------------

// !! DO NOT USE var $title
// [] operator not supported for strings in /modules/forums/inc/forums.sections.php:265
// $title[] = [cot_url('forums'), Cot::$L['Forums']];

// Условие: мы находимся в модуле forums (location), режим 'sections' (главная), и параметр 'c' не передан
if (($env['location'] ?? '') === 'forums' && ($m ?? '') === 'sections' && empty($_GET['c'])) {

    // === ЗАГОЛОВОК СТРАНИЦЫ (TITLE) ===
    // Проверяем, задан ли мета-заголовок по умолчанию для всех категорий форума (cat___default)
    if (!empty(Cot::$cfg['forums']['cat___default']['metatitle'])) {
        // Если мета-заголовок есть, используем его
        $seoforums_sections_title = Cot::$cfg['forums']['cat___default']['metatitle'];
    } else {
        // Если мета-заголовок не задан, берём языковую строку-заглушку для главной форума
        $seoforums_sections_title = Cot::$L['seoforums_main_empty_meta_title'];
    }
    // Передаём заголовок в шаблон, экранируя спецсимволы для безопасного HTML
    $t->assign('HEADER_TITLE', htmlspecialchars($seoforums_sections_title));

    // === META DESCRIPTION ===
    // Проверяем, задано ли общее мета-описание для категорий форума
    if (!empty(Cot::$cfg['forums']['cat___default']['metadesc'])) {
        // Если мета-описание задано, используем его
        $desc = Cot::$cfg['forums']['cat___default']['metadesc'];
    } else {
        // Если мета-описание не задано, берём языковую строку-заглушку для главной форума
        $desc = Cot::$L['seoforums_main_empty_meta_description'];
    }
    // Очищаем описание от HTML-тегов, экранируем и передаём в шаблон
    $t->assign('HEADER_META_DESCRIPTION', htmlspecialchars(strip_tags($desc)));

    // === META KEYWORDS ===
    // Проверяем, заданы ли общие ключевые слова для категорий форума
    if (!empty(Cot::$cfg['forums']['cat___default']['keywords'])) {
        // Если ключевые слова заданы, передаём их в шаблон
        $t->assign('HEADER_META_KEYWORDS', Cot::$cfg['forums']['cat___default']['keywords']);
    }

    // === CANONICAL URL ===
    // Формируем абсолютный канонический URL главной страницы форума (без параметров)
    $canonical = Cot::$cfg['mainurl'] . '/' . cot_url('forums');
    // Передаём канонический URL в шаблон
    $t->assign('HEADER_CANONICAL_URL', $canonical);
}

// ----------------------------------------------------------
// СЕКЦИЯ 3b: Страница конкретной категории (c передан и существует)
// ----------------------------------------------------------

// !! DO NOT USE var $title
// [] operator not supported for strings in /modules/forums/inc/forums.sections.php:265
// $title[] = [cot_url('forums'), Cot::$L['Forums']];

// Условие: мы находимся в модуле forums (location), режим 'sections' (главная), и параметр 'c' передан
if (($env['location'] ?? '') === 'forums' && ($m ?? '') === 'sections' && !empty($_GET['c'])) {
    // Получаем код категории из GET-параметра
    $c = $_GET['c'];
    // Проверяем, что категория с таким кодом существует в структуре форумов
    if (isset(Cot::$structure['forums'][$c])) {

        // === ЗАГОЛОВОК СТРАНИЦЫ (TITLE) ===
        // Приоритет: индивидуальный мета-заголовок категории
        if (!empty(Cot::$cfg['forums']['cat_' . $c]['metatitle'])) {
            $seoforums_sections_title = Cot::$cfg['forums']['cat_' . $c]['metatitle'];
        // Если мета-заголовка нет, используем название категории из структуры
        } elseif (!empty(Cot::$structure['forums'][$c]['title'])) {
            $seoforums_sections_title = Cot::$structure['forums'][$c]['title'];
        // Если и названия нет, берём языковую строку-заглушку для категорий
        } else {
            $seoforums_sections_title = Cot::$L['seoforums_sections_empty_meta_title'];
        }
        // Передаём заголовок в шаблон, экранируя спецсимволы
        $t->assign('HEADER_TITLE', htmlspecialchars($seoforums_sections_title));

        // === META DESCRIPTION ===
        // Приоритет: индивидуальное мета-описание категории
        if (!empty(Cot::$cfg['forums']['cat_' . $c]['metadesc'])) {
            $desc = Cot::$cfg['forums']['cat_' . $c]['metadesc'];
        // Если мета-описания нет, используем описание категории из структуры
        } elseif (!empty(Cot::$structure['forums'][$c]['desc'])) {
            $desc = Cot::$structure['forums'][$c]['desc'];
        // Если описания категории тоже нет, берём языковую строку-заглушку
        } else {
            $desc = Cot::$L['seoforums_sections_empty_meta_description'];
        }
        // Очищаем описание от HTML-тегов, экранируем и передаём в шаблон
        $t->assign('HEADER_META_DESCRIPTION', htmlspecialchars(strip_tags($desc)));

        // === META KEYWORDS ===
        // Проверяем, заданы ли ключевые слова в настройках этой категории
        if (!empty(Cot::$cfg['forums']['cat_' . $c]['keywords'])) {
            // Если заданы, передаём их в шаблон
            $t->assign('HEADER_META_KEYWORDS', Cot::$cfg['forums']['cat_' . $c]['keywords']);
        }

        // === CANONICAL URL ===
        // Формируем абсолютный канонический URL для страницы этой категории (с параметром 'c')
        $canonical = Cot::$cfg['mainurl'] . '/' . cot_url('forums', ['c' => $c]);
        // Передаём канонический URL в шаблон
        $t->assign('HEADER_CANONICAL_URL', $canonical);
    }
}
