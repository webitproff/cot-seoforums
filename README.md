# SEO Forum — SEO Plugin for the Forums Module of Cotonti

**Author:** [webitproff](https://github.com/webitproff)  
**Date:** 2026‑07‑04  
**Copyright:** © webitproff, 2025‑2026  
**[Repository](https://github.com/webitproff/cot-seoforums)**  
**License:** BSD 3‑Clause License

[![Version](https://img.shields.io/badge/version-2.1.1-green.svg)](https://github.com/webitproff/cot-seoforums/releases)
[![Cotonti Compatibility](https://img.shields.io/badge/Cotonti-v.1+-orange.svg)](https://github.com/Cotonti/Cotonti)
[![PHP](https://img.shields.io/badge/PHP-8.4+-purple.svg)](https://www.php.net/releases/8_4_0.php)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)](https://www.mysql.com/)
[![Bootstrap v5.3+](https://img.shields.io/badge/Bootstrap-v5.3+-blueviolet.svg)](https://getbootstrap.com/)
[![Font Awesome v7](https://img.shields.io/badge/Font%20Awesome-v7-blue.svg)](https://fontawesome.com/)
[![License](https://img.shields.io/badge/license-BSD%203--Clause-blue.svg)](https://github.com/webitproff/cot-seoforums/blob/main/LICENSE)

---

## 1. Purpose of the plugin

SEO Forum is a comprehensive SEO solution for the “Forums” module of Cotonti.  
The plugin automatically generates all necessary meta tags (`title`, `description`, `keywords`), social cards (Open Graph, Twitter Cards), Schema.org structured data (`DiscussionForumPosting`), as well as a “Related Topics” block, reading time, author information, and flexible canonical URL management.

The plugin works on all forum pages:

- topic post list;
- single post (with proper canonical when `forums_singlepost` is active);
- section topic list;
- forum main page;
- individual category (section) pages.

---

## 2. Key Features

- **Smart meta descriptions**  
  `meta name="description"` is built according to the pattern: `Category name. Description text`. The description text is truncated without breaking words — up to the nearest period, comma, exclamation mark or question mark after the 160th character; if no punctuation is found, it truncates to the last space.

- **Open Graph and Twitter Cards**  
  `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name`, `og:locale`, as well as `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image` are automatically output.

- **Schema.org structured data**  
  JSON‑LD of type `DiscussionForumPosting` is generated with fields `headline`, `description` (up to 2000 characters from the post text), `keywords`, `author`, `publisher`, `datePublished`, `dateModified`, `image`, `mainEntityOfPage`.

- **Canonical URL management**  
  For a single post (when the `forums_singlepost` plugin is active) the canonical URL points to the specific post page rather than the whole topic. For other pages the canonical is built correctly (topic, section, category, forum main page).

- **Related Topics**  
  A “Related Topics” block is displayed on the topic page (up to 3 topics, configurable). Each topic shows an image (from `attacher` attachments or a placeholder), title, description (smart truncated) and author.

- **Reading time and topic author**  
  The reading time of the first post is calculated (200 words/min, minimum 1 min). The topic author (or the author of the specific post for single pages) is shown.

- **Custom title and meta tags override**  
  Custom `title`, `description`, `keywords` are set on topic lists, section pages and the main forum page according to category settings or the plugin’s general settings.

- **Localization**  
  Full support for Russian and English languages. Default strings for empty meta tags are provided.

---

## 3. File structure

```
plugins/seoforums/
├── inc/
│   └── seoforums.functions.php          — functions (keyword extraction, reading time, image retrieval)
├── lang/
│   ├── seoforums.ru.lang.php            — Russian translation and stop words
│   └── seoforums.en.lang.php            — English translation and stop words
├── seoforums.setup.php                  — plugin registration and configuration
├── seoforums.forums.posts.main.php       — SEO tags for post list and single post (Open Graph, Twitter, Schema.org, canonical)
├── seoforums.forums.posts.tags.php       — template tags (related topics, reading time, author)
├── seoforums.header.tags.php             — override of meta tags in <head> for all forum pages
└── seoforums.global.php                  — (optional) additional global actions
```

---

## 4. How it works

### Smart description trimming
When generating `meta description` and descriptions in microdata, the following algorithm is applied:

1. The source text is taken (topic description or post text).
2. If its length exceeds 160 characters, a period, comma, `!` or `?` is searched for in the range from 160 to 250 characters.
3. If a punctuation mark is found — the text is trimmed up to and including it (without breaking words).
4. If no punctuation mark is found — we trim to the last space before the 160th character, preserving word integrity.
5. The category name with a period is prepended: `Category. Description…`.

### Integration with `forums_singlepost`
When the `forums_singlepost` plugin is active on a single post page:

- `og:url` and `canonical` point to **the post itself**, not the topic.
- `meta description` and `JSON-LD description` are generated from the text of **the specific post**.
- The author in microdata corresponds to the post author, not the first message of the topic.
- The image is taken from the post attachments; if none exist — from the topic.

### Related Topics
The `RELATED_TOPICS` block is displayed in the forum template. For each related topic:

- It checks whether a description (`ft_desc`) exists — if not, the text of the first post is used.
- The description is smart trimmed (using the same algorithm as `meta description`) and prepended with the category name.
- The image is obtained from the `attacher` attachments of the first post, or a placeholder is used.
- The author is determined by `ft_firstposterid`.

---

## 5. Requirements

- **Cotonti CMF** version 1.0 or newer (tested on 0.9.26+)
- **PHP** 8.4 or higher
- **MySQL** 8.0+ (or MariaDB with InnoDB support)
- The **forums** module must be installed and enabled
- Recommended plugins:
  - `attacher` — for extracting images from posts
  - `urleditor` — for SEO‑friendly URLs

---

## 6. Installation

1. Copy the `seoforums` folder to the `plugins/` directory of your site.
2. Go to **Admin Panel → Extensions** and install the **SEO Forum** plugin.
3. Configure the settings:
   - `placeholderlogo` — path to a small logo for microdata (e.g., `themes/index36/img/logo.webp`)
   - `placeholderimagedefault` — path to a large placeholder image for topics without an image
   - `maxrelatedpostsperpage` — number of related topics (0 to disable the block)
4. In the `header.tpl` template, make sure the tags `{HEADER_META_DESCRIPTION}`, `{HEADER_META_KEYWORDS}` and `{PHP.out.meta}` are present.
   Example:
```html
<!--
	/********************************************************************************
	* File: header.tpl
	* Extension: Core'
	* Description: HTML template for header.tpl.
	* Compatibility: CMF/CMS Cotonti Siena v0.9.26[](https://github.com/Cotonti/Cotonti)
	* Dependencies: 
	* 		 Bootstrap 5.3.+[](https://getbootstrap.com/); 
	* 		 Font Awesome Free 7.1[](https://fontawesome.com/)
	* Theme: Index36  
	* Version: 1.0.2 
	* Created: 01 Feb 2026 
	* Updated: 04 July 2026 
	* Copyright (c) 2026 webitproff | https://github.com/webitproff
	* Source: https://github.com/webitproff/index36-cotonti-theme
	* Demo : https://freelance-script.abuyfile.com/ 
	* Help and support: https://abuyfile.com/ru/forums/cotonti/original/skins/index36
	* License: BSD (Free distribution with saving Copyright (c) 2026 webitproff)  
	********************************************************************************/
-->
<!-- BEGIN: HEADER -->
<!DOCTYPE html>
	<!-- IF {HTML_LANG} -->
	<html lang="{HTML_LANG}" data-bs-theme="light">
	<!-- ELSE -->
	<html lang="{PHP.usr.lang}" data-bs-theme="light">
	<!-- ENDIF -->
<!-- main header -->
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- IF {I18N_HEADER_META_TITLE} --> 
		<title>{I18N_HEADER_META_TITLE}</title>
		<!-- ELSE -->
		<title>{HEADER_TITLE} <!-- IF {MARKET_HEADER_XTRA_DEMO_COUNTRY} -->{MARKET_HEADER_XTRA_DEMO_COUNTRY_NAME} <!-- ENDIF --></title>
		<!-- ENDIF -->
	<!-- IF {I18N_HEADER_META_DESCRIPTION} --> 
		<meta name="description" content="{I18N_HEADER_META_DESCRIPTION}" />
	<!-- ELSE -->
		<!-- IF {HEADER_META_DESCRIPTION} -->
		<meta name="description" content="{HEADER_META_DESCRIPTION}" />
		<!-- ENDIF -->
	<!-- ENDIF -->
		<!-- IF {HEADER_BASEHREF} -->
		{HEADER_BASEHREF}
		<!-- ENDIF -->
		<!-- IF {HEADER_CANONICAL_URL} -->
		<link rel="canonical" href="{HEADER_CANONICAL_URL}" />
		<!-- ENDIF -->
		<!-- IF {ALTERNATE_TAGS} -->
		{ALTERNATE_TAGS}
		<!-- ENDIF -->
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="icon" href="{PHP.cfg.themes_dir}/{PHP.theme}/img/icon.webp" type="image/svg+xml">
		<link rel="apple-touch-icon" href="apple-touch-icon.png" />
		<!-- IF {PHP.out.meta} -->
		{PHP.out.meta}
		<!-- ENDIF -->
		<script>
			(function () {
				const storedTheme = localStorage.getItem('theme');
				const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
				const defaultTheme = storedTheme || (prefersDark ? 'dark' : 'light');
				document.documentElement.setAttribute('data-bs-theme', defaultTheme);
			})();
		</script>
		{HEADER_HEAD}
	</head>
		<body>
```

5. In the `forums.posts.tpl` template, add the `RELATED_TOPICS` block (example in the documentation) and the tags `{TOPIC_READ_TIME}`, `{TOPIC_AUTHOR}` as needed.
   For example, after the reply form, insert:
```html
<!-- IF {PHP|cot_plugin_active('seoforums')} -->
<!-- BEGIN: RELATED_TOPICS -->
<div class="container mb-4 mt-5">
    <h3 class="h4 mt-3">{PHP.L.seoforums_related}</h3>
    <div class="row g-3">
        <!-- BEGIN: RELATED_ROW -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="{RELATED_TOPIC_ROW_URL}" class="card border-0 shadow-sm text-decoration-none h-100 overflow-hidden">
                <!-- Image cropped to 1200:630 aspect ratio -->
                <div class="related-img-wrapper" style="aspect-ratio: 1200 / 630; width: 100%; overflow: hidden;">
                    <img src="{RELATED_TOPIC_ROW_IMAGE}" 
					alt="{RELATED_TOPIC_ROW_TITLE}" 
					class="related-img"
					style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
				</div>
                <!-- Card content -->
                <div class="card-body">
                    <h5 class="fs-6 fw-semibold mb-1 text-body">{RELATED_TOPIC_ROW_TITLE}</h5>
                    <!-- IF {RELATED_TOPIC_ROW_DESC} -->
                    <p class="text-muted small mb-1">{RELATED_TOPIC_ROW_DESC}</p>
                    <!-- ENDIF -->
                    <p class="text-muted small mb-0">{RELATED_TOPIC_ROW_AUTHOR}</p>
				</div>
			</a>
		</div>
        <!-- END: RELATED_ROW -->
	</div>
</div>
<!-- END: RELATED_TOPICS -->
<!-- ENDIF -->
```
6. Clear the system cache.

---

## 7. Recommendations

- Install the `attacher` plugin for proper extraction of images from posts.
- Adjust the stop‑words in the language file to improve keyword extraction quality.
- If you use **[forums_singlepost](https://github.com/webitproff/forums-singlepost-tpl-cotonti)** for separate single post templates, the plugin will automatically adjust canonical URLs and meta tags.
- For a large number of posts, ensure that the `cot_forum_topics`, `cot_forum_posts` and `cot_structure` tables have the necessary indexes.

---
## 8. Support
**[Help and discussion](https://abuyfile.com/index.php?e=forums&m=posts&q=171&l=ru)**

---

## 9. License

BSD 3‑Clause License.  
Author: [webitproff](https://github.com/webitproff), Copyright © 2025‑2026.

**Repository:** [https://github.com/webitproff/cot-seoforums](https://github.com/webitproff/cot-seoforums)



___
РУССКИЙ
___

# SEO Forum — SEO‑плагин для модуля форумов Cotonti

**Автор:** [webitproff](https://github.com/webitproff)  
**Дата:** 2026‑07‑04  
**Copyright:** © webitproff, 2025‑2026  
**[Репозиторий](https://github.com/webitproff/cot-seoforums)**  
**Лицензия:** BSD 3‑Clause License

[![Version](https://img.shields.io/badge/version-2.1.1-green.svg)](https://github.com/webitproff/cot-seoforums/releases)
[![Cotonti Compatibility](https://img.shields.io/badge/Cotonti-v.1+-orange.svg)](https://github.com/Cotonti/Cotonti)
[![PHP](https://img.shields.io/badge/PHP-8.4+-purple.svg)](https://www.php.net/releases/8_4_0.php)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)](https://www.mysql.com/)
[![Bootstrap v5.3+](https://img.shields.io/badge/Bootstrap-v5.3+-blueviolet.svg)](https://getbootstrap.com/)
[![Font Awesome v7](https://img.shields.io/badge/Font%20Awesome-v7-blue.svg)](https://fontawesome.com/)
[![License](https://img.shields.io/badge/license-BSD%203--Clause-blue.svg)](https://github.com/webitproff/cot-seoforums/blob/main/LICENSE)

---

## 1. Назначение плагина

SEO Forum — это комплексное SEO‑решение для модуля «Форумы» Cotonti.  
Плагин автоматически формирует все необходимые мета‑теги (`title`, `description`, `keywords`), социальные карточки (Open Graph, Twitter Cards), структурированные данные Schema.org (`DiscussionForumPosting`), а также добавляет блок «Похожие темы», время чтения, информацию об авторе и гибко управляет каноническими URL.

Плагин работает на всех страницах форума:

- список постов темы;
- одиночный пост (с корректным canonical при активном `forums_singlepost`);
- список тем раздела;
- главная страница форума;
- страницы отдельных категорий (секций).

---

## 2. Основные возможности

- **Умные мета‑описания**  
  `meta name="description"` формируется по схеме: `Название категории. Текст описания`. Текст описания обрезается без разрыва слов — до ближайшей точки, запятой, восклицательного или вопросительного знака после 160‑го символа, а если знак не найден — до последнего пробела.

- **Open Graph и Twitter Cards**  
  Автоматически выводятся `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name`, `og:locale`, а также `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`.

- **Структурированные данные Schema.org**  
  Генерируется JSON‑LD типа `DiscussionForumPosting` с полями `headline`, `description` (до 2000 символов из текста поста), `keywords`, `author`, `publisher`, `datePublished`, `dateModified`, `image`, `mainEntityOfPage`.

- **Управление каноническими URL**  
  Для одиночного поста (при активном плагине `forums_singlepost`) канонический URL указывает на страницу конкретного поста, а не на всю тему. Для остальных страниц каноникал строится корректно (тема, раздел, категория, главная форума).

- **Похожие темы**  
  На странице темы выводится блок «Похожие темы» (до 3 тем, настраивается). Каждая тема показывает изображение (из вложений `attacher` или плейсхолдер), заголовок, описание (с умной обрезкой) и автора.

- **Время чтения и автор темы**  
  Рассчитывается время чтения первого поста (200 слов/мин, минимум 1 мин). Отображается автор темы (или автор конкретного поста для одиночной страницы).

- **Переопределение заголовков и мета‑тегов**  
  На страницах списка тем, разделов и главной форума устанавливаются кастомные `title`, `description`, `keywords` в соответствии с настройками категорий или общими настройками плагина.

- **Локализация**  
  Полная поддержка русского и английского языков. Предусмотрены дефолтные строки для пустых мета‑тегов.

---

## 3. Структура файлов

```
plugins/seoforums/
├── inc/
│   └── seoforums.functions.php          — функции (извлечение ключевых слов, время чтения, получение изображений)
├── lang/
│   ├── seoforums.ru.lang.php            — русский перевод и стоп‑слова
│   └── seoforums.en.lang.php            — английский перевод и стоп‑слова
├── seoforums.setup.php                  — регистрация и конфигурация плагина
├── seoforums.forums.posts.main.php       — SEO‑теги для списка постов и одиночного поста (Open Graph, Twitter, Schema.org, canonical)
├── seoforums.forums.posts.tags.php       — теги для шаблонов (похожие темы, время чтения, автор)
├── seoforums.header.tags.php             — переопределение мета‑тегов в <head> для всех страниц форума
└── seoforums.global.php                  — (опционально) дополнительные глобальные действия
```

---

## 4. Как это работает

### Умная обрезка описаний
При формировании `meta description` и описаний в микроразметке применяется алгоритм:

1. Берётся исходный текст (описание темы или текст поста).
2. Если его длина больше 160 символов, ищется точка, запятая, `!` или `?` в диапазоне от 160 до 250 символов.
3. Если знак найден — текст обрезается до него включительно (без разрыва слова).
4. Если знак не найден — обрезаем до последнего пробела перед 160‑м символом, сохраняя целостность слова.
5. В начало добавляется название категории с точкой: `Категория. Описание…`.

### Интеграция с `forums_singlepost`
При активном плагине `forums_singlepost` на странице одиночного поста:

- `og:url` и `canonical` ссылаются на **сам пост**, а не на тему.
- `meta description` и `JSON-LD description` формируются из текста **конкретного поста**.
- Автор в микроразметке соответствует автору поста, а не первому сообщению темы.
- Изображение подбирается из вложений поста, а если их нет — из темы.

### Похожие темы
Блок `RELATED_TOPICS` выводится в шаблоне форума. Для каждой похожей темы:

- Проверяется, есть ли описание (`ft_desc`) — если нет, берётся текст первого поста.
- Описание умно обрезается (по тому же алгоритму, что и `meta description`) и дополняется названием категории.
- Изображение получается из вложений `attacher` первого поста, либо используется плейсхолдер.
- Автор определяется по `ft_firstposterid`.

---

## 5. Требования

- **Cotonti CMF** версии 1.0 или новее (протестировано на 0.9.26+)
- **PHP** 8.4 или выше
- **MySQL** 8.0+ (или MariaDB с поддержкой InnoDB)
- Модуль **forums** должен быть установлен и активирован
- Рекомендуемые плагины:
  - `attacher` — для извлечения изображений из постов
  - `urleditor` — для SEO‑дружественных URL

---

## 6. Установка

1. Скопируйте папку `seoforums` в директорию `plugins/` вашего сайта.
2. Перейдите в **Админ‑панель → Расширения** и установите плагин **SEO Forum**.
3. Настройте конфигурацию:
   - `placeholderlogo` — путь к маленькому логотипу для микроразметки (например, `themes/index36/img/logo.webp`)
   - `placeholderimagedefault` — путь к большой картинке‑плейсхолдеру для тем без изображения
   - `maxrelatedpostsperpage` — количество похожих тем (0 — отключить блок)
4. В шаблоне `header.tpl` убедитесь, что присутствуют теги `{HEADER_META_DESCRIPTION}`, `{HEADER_META_KEYWORDS}` и `{PHP.out.meta}`.
   пример:
```
<!--
	/********************************************************************************
	* File: header.tpl
	* Extension: Core'
	* Description: HTML template for header.tpl.
	* Compatibility: CMF/CMS Cotonti Siena v0.9.26[](https://github.com/Cotonti/Cotonti)
	* Dependencies: 
	* 		 Bootstrap 5.3.+[](https://getbootstrap.com/); 
	* 		 Font Awesome Free 7.1[](https://fontawesome.com/)
	* Theme: Index36  
	* Version: 1.0.2 
	* Created: 01 Feb 2026 
	* Updated: 04 July 2026 
	* Copyright (c) 2026 webitproff | https://github.com/webitproff
	* Source: https://github.com/webitproff/index36-cotonti-theme
	* Demo : https://freelance-script.abuyfile.com/ 
	* Help and support: https://abuyfile.com/ru/forums/cotonti/original/skins/index36
	* License: BSD (Free distribution with saving Copyright (c) 2026 webitproff)  
	********************************************************************************/
-->
<!-- BEGIN: HEADER -->
<!DOCTYPE html>
	<!-- IF {HTML_LANG} -->
	<html lang="{HTML_LANG}" data-bs-theme="light">
	<!-- ELSE -->
	<html lang="{PHP.usr.lang}" data-bs-theme="light">
	<!-- ENDIF -->
<!-- main header -->
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- IF {I18N_HEADER_META_TITLE} --> 
		<title>{I18N_HEADER_META_TITLE}</title>
		<!-- ELSE -->
		<title>{HEADER_TITLE} <!-- IF {MARKET_HEADER_XTRA_DEMO_COUNTRY} -->{MARKET_HEADER_XTRA_DEMO_COUNTRY_NAME} <!-- ENDIF --></title>
		<!-- ENDIF -->
	<!-- IF {I18N_HEADER_META_DESCRIPTION} --> 
		<meta name="description" content="{I18N_HEADER_META_DESCRIPTION}" />
	<!-- ELSE -->
		<!-- IF {HEADER_META_DESCRIPTION} -->
		<meta name="description" content="{HEADER_META_DESCRIPTION}" />
		<!-- ENDIF -->
	<!-- ENDIF -->
		<!-- IF {HEADER_BASEHREF} -->
		{HEADER_BASEHREF}
		<!-- ENDIF -->
		<!-- IF {HEADER_CANONICAL_URL} -->
		<link rel="canonical" href="{HEADER_CANONICAL_URL}" />
		<!-- ENDIF -->
		<!-- IF {ALTERNATE_TAGS} -->
		{ALTERNATE_TAGS}
		<!-- ENDIF -->
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="icon" href="{PHP.cfg.themes_dir}/{PHP.theme}/img/icon.webp" type="image/svg+xml">
		<link rel="apple-touch-icon" href="apple-touch-icon.png" />
		<!-- IF {PHP.out.meta} -->
		{PHP.out.meta}
		<!-- ENDIF -->
		<script>
			(function () {
				const storedTheme = localStorage.getItem('theme');
				const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
				const defaultTheme = storedTheme || (prefersDark ? 'dark' : 'light');
				document.documentElement.setAttribute('data-bs-theme', defaultTheme);
			})();
		</script>
		{HEADER_HEAD}
	</head>
		<body>
```

5. В шаблоне `forums.posts.tpl` добавьте блок `RELATED_TOPICS` (пример есть в документации) и теги `{TOPIC_READ_TIME}`, `{TOPIC_AUTHOR}` при необходимости.
   например, после формы для ответов, вставить:
```
<!-- IF {PHP|cot_plugin_active('seoforums')} -->
<!-- BEGIN: RELATED_TOPICS -->
<div class="container mb-4 mt-5">
    <h3 class="h4 mt-3">{PHP.L.seoforums_related}</h3>
    <div class="row g-3">
        <!-- BEGIN: RELATED_ROW -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="{RELATED_TOPIC_ROW_URL}" class="card border-0 shadow-sm text-decoration-none h-100 overflow-hidden">
                <!-- Картинка с обрезкой по пропорции 1200:630 -->
                <div class="related-img-wrapper" style="aspect-ratio: 1200 / 630; width: 100%; overflow: hidden;">
                    <img src="{RELATED_TOPIC_ROW_IMAGE}" 
					alt="{RELATED_TOPIC_ROW_TITLE}" 
					class="related-img"
					style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
				</div>
                <!-- Контент карточки -->
                <div class="card-body">
                    <h5 class="fs-6 fw-semibold mb-1 text-body">{RELATED_TOPIC_ROW_TITLE}</h5>
                    <!-- IF {RELATED_TOPIC_ROW_DESC} -->
                    <p class="text-muted small mb-1">{RELATED_TOPIC_ROW_DESC}</p>
                    <!-- ENDIF -->
                    <p class="text-muted small mb-0">{RELATED_TOPIC_ROW_AUTHOR}</p>
				</div>
			</a>
		</div>
        <!-- END: RELATED_ROW -->
	</div>
</div>
<!-- END: RELATED_TOPICS -->
<!-- ENDIF -->
```
6. Очистите системный кэш.

---

## 7. Рекомендации

- Для корректной вставки изображений из постов установите плагин `attacher`.
- Настройте стоп‑слова в языковом файле, чтобы улучшить качество извлечения ключевых слов.
- Если вы используете **[forums_singlepost](https://github.com/webitproff/forums-singlepost-tpl-cotonti)** для раздельных шаблонов одиночных постов, плагин автоматически подстроит канонические URL и мета‑теги.
- При большом количестве постов убедитесь, что таблицы `cot_forum_topics`, `cot_forum_posts` и `cot_structure` имеют необходимые индексы.

---
## 8. Поддержка
**[помощь и обсуждение](https://abuyfile.com/index.php?e=forums&m=posts&q=171&l=ru)**

---

## 9. Лицензия

BSD 3‑Clause License.  
Автор: [webitproff](https://github.com/webitproff), Copyright © 2025‑2026.

**Репозиторий:** [https://github.com/webitproff/cot-seoforums](https://github.com/webitproff/cot-seoforums)
