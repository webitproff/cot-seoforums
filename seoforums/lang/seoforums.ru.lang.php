<?php
/**
 * SEO Forum plugin – Russian localization
 *
 * File:           plugins/seoforums/lang/seoforums.ru.lang.php
 * Purpose:        Provides Russian translations for plugin configuration descriptions,
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
$L['cfg_placeholderlogo'] = 'Лого (маленькая картинка)';
$L['cfg_placeholderlogo_hint'] = 'Относительный путь к изображению, например <code>themes/index36/img/logo.webp</code>. Используется в микроразметке publisher.';
$L['cfg_placeholderimagedefault'] = 'Картинка топика (темы) по умолчанию';
$L['cfg_placeholderimagedefault_hint'] = 'Относительный путь к изображению, например <code>/datas/images/static/logo-big.webp</code>. Показывается, если у темы нет собственного изображения.';
$L['cfg_maxrelatedpostsperpage'] = 'Максимум похожих тем';
$L['cfg_maxrelatedpostsperpage_hint'] = 'Количество выводимых записей в блоке <code>RELATED_TOPICS</code> на странице топика. Значение 0 отключает блок.';

// === Plugin description ===
$L['info_name'] = 'SEO Forum и микроразметка';
$L['info_desc'] = 'Расширенные SEO-инструменты для модуля форумов: умные мета-описания, Open Graph, Twitter Cards, Schema.org DiscussionForumPosting, управление canonical, похожие темы с картинками, время чтения, автор и многое другое.';
$L['info_notes'] = 'Требуется модуль forums. Рекомендуется установить плагины attacher (для изображений) и urleditor (для SEO-ссылок). Тестировалось на Cotonti v.1+ PHP 8.4+.';

// === Default texts for empty meta (fallback) ===
$L['seoforums_topics_empty_meta_title'] = 'Список тем раздела';
$L['seoforums_topics_empty_meta_description'] = 'Просмотрите все темы этого раздела форума.';
$L['seoforums_main_empty_meta_title'] = 'Форумы интернет-магазина';
$L['seoforums_main_empty_meta_description'] = 'Форумы сообщества';
$L['seoforums_sections_empty_meta_title'] = 'Раздел форума';
$L['seoforums_sections_empty_meta_description'] = 'Описание раздела отсутствует';

// === Frontend strings ===
$L['seoforums_read_time'] = 'мин чтения';
$L['seoforums_unknown_author'] = 'Неизвестный автор';
$L['seoforums_related'] = 'Похожие темы';

// === Stop words for keyword extraction ===
$L['seoforums_stop_words'] = 'а,без,более,бы,был,была,были,было,быть,в,вам,вас,весь,во,вот,все,всего,всех,вы,где,да,даже,для,до,его,ее,если,есть,еще,же,за,здесь,и,из,или,им,их,к,как,когда,кто,ли,либо,мне,может,мы,на,надо,наш,не,него,нее,нет,ни,них,но,ну,о,об,однако,он,она,они,оно,от,перед,по,под,после,потом,потому,почти,при,про,раз,разве,с,сам,свое,свои,себе,себя,сей,сколько,со,совсем,так,там,те,тем,теперь,то,того,тоже,той,только,том,ты,у,уже,хотя,чего,чей,чем,что,чтобы,чье,чья,эта,эти,это,я';
