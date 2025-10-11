# cot-seoforums

# SEO Forum Plugin for Cotonti

The **SEO Forum** plugin enhances the SEO capabilities of the forums module in [Cotonti CMF](https://github.com/Cotonti/Cotonti) version 0.9.26 on PHP 8.4. It adds meta tags, Open Graph, Twitter Card, Schema.org structured data, keyword extraction, reading time estimation, and displays related topics on forum topic pages.

## Tested
Cotonti CMF v.0.9.26 on PHP v.8.4

## File Structure
```
plugins/seoforums/
├── inc/
│   └── seoforums.functions.php # Functions for keyword extraction and reading time estimation
├── lang/
│   ├── seoforums.ru.lang.php # Russian translations and stop words for keyword extraction
│   └── seoforums.en.lang.php # English translations and stop words for keyword extraction
├── seoforums.forums.posts.php # Adds Open Graph, Twitter Card, and Schema.org meta tags
├── seoforums.forums.tags.php # Defines page tags (TOPIC_READ_TIME, TOPIC_AUTHOR, RELATED_TOPIC_ROW_*)
├── seoforums.header.tags.php # Overrides HEADER_META_DESCRIPTION and HEADER_META_KEYWORDS
└── seoforums.setup.php # Plugin configuration and setup
```

### File Description
- **seoforums.setup.php**: Defines plugin metadata (name, version, author) and configuration (image paths, number of related topics).
- **seoforums.functions.php**: Contains functions:
  - `cot_extract_keywords_forums($text, $limit = 10)`: Extracts keywords from text, removing HTML, converting to lowercase, excluding stop words, and sorting by frequency.
  - `cot_estimate_read_time_forums($text)`: Estimates reading time, removing HTML/BB codes, counting words (200 words/minute, minimum 1 minute).
- **seoforums.header.tags.php**: Overrides `{HEADER_META_DESCRIPTION}` and `{HEADER_META_KEYWORDS}` for meta tags on forum topic pages.
- **seoforums.forums.posts.php**: Generates Open Graph, Twitter Card, and Schema.org meta tags for topic pages, including title, description, keywords, author, category, and images.
- **seoforums.forums.tags.php**: Defines tags for the `forums.posts.tpl` template, such as `{TOPIC_READ_TIME}`, `{TOPIC_AUTHOR}`, `{RELATED_TOPIC_ROW_URL}`, `{RELATED_TOPIC_ROW_TITLE}`, `{RELATED_TOPIC_ROW_DESC}`, `{RELATED_TOPIC_ROW_IMAGE}`, `{RELATED_TOPIC_ROW_AUTHOR}`.
- **seoforums.ru.lang.php**: Contains Russian translations ("Related topics", "min read", "Unknown author") and stop words for keyword extraction.
- **seoforums.en.lang.php**: Contains English translations (e.g., "Related topics", "min read", "Unknown author") and stop words for keyword extraction.

## Features
- **Meta Tags**:
  - Generates `<meta name="description">` (category + topic description or first post text, truncated to 160 characters) and `<meta name="keywords">` (extracted keywords).
  - Ensures description is a single line without breaks.
- **Open Graph and Twitter Card**:
  - Adds tags `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name`, `og:locale`, `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image` for social media sharing.
- **Schema.org**:
  - Includes structured data for the `DiscussionForumPosting` type (`headline`, `description`, `keywords`, `author`, `publisher`, `image`, `datePublished`, `dateModified`, `mainEntityOfPage`).
- **Keyword Extraction**:
  - Extracts up to 10 keywords from the first post's text using `cot_extract_keywords_forums`, excluding stop words from `seoforums.ru.lang.php` or `seoforums.en.lang.php`.
- **Reading Time Estimation**:
  - Calculates the reading time of the first post using `cot_estimate_read_time_forums` (200 words/minute, minimum 1 minute).
- **Related Topics**:
  - Displays up to 3 (configurable) related topics from the same or other categories, with title, description, image (`ft_image` or placeholder), and author.
- **Topic Author**:
  - Shows the author's name from `cot_users` by `ft_firstposterid` or "Unknown author" if data is unavailable.

## Functionality
- **Microdata**: Adds JSON-LD structured data for the "DiscussionForumPosting" type, including title, description, keywords, author, publisher, publication and modification dates, image, and page link.
- **Open Graph and Twitter Card**: Generates meta tags for social media, including title, description, URL, image, and content type.
- **Related Topics**: Displays a block of related topics (RELATED_TOPICS) on the forum topic page. The number of topics is configurable (default is 3). Topics are sourced from the same category if possible, otherwise from other categories. Each related topic includes URL, title, description (from ft_desc or first post), image (ft_image or placeholder), and author.
- **Reading Time**: Calculates the estimated reading time of the first post based on word count (average speed of 200 words per minute, minimum 1 minute). Displayed in the template as "X min read".
- **Keywords**: Extracts keywords from the first post's text, excluding stop words (Russian or English list), considering frequency and word length (>2 characters). Returns up to 10 keywords.
- **Topic Author**: Identifies the author by ft_firstposterid, or "Unknown author" if not found.
- **Meta Tag Override**: Sets meta description (category + description, truncated to 160 characters) and meta keywords (extracted keywords) on forum topic pages.
- **Images**: Uses placeholders for the logo (small image) and topic image (large image), configurable in settings.

## Installation

### Requirements
- Cotonti CMF v.0.9.26.
- `forums` module enabled.
- MySQL/MariaDB database.
- Recommended plugins: `attacher`, `urleditor`.

### Steps
1. **Download the Plugin**:
   - Clone or download the repository from GitHub:
     ```bash
     git clone https://github.com/webitproff/cot-seoforums.git
     ```
   - Or download the ZIP and extract it to `/plugins/`.
2. **Copy Files**:
   - Place the `seoforums` folder in the `/plugins/` directory of your Cotonti installation:
     ```
     public_html/plugins/seoforums/
     ```
3. **Install the Plugin**:
   - Go to **Administration → Extensions** in the Cotonti admin panel.
   - Find **SEO Forum** in the list and click **Install**.
   - The plugin will be registered using `seoforums.setup.php`.
4. **Configure Settings**:
   - Go to **Administration → Extensions → seoforums → Configuration**.
   - Specify:
     - `placeholderlogo`: Path to the logo (e.g., `/images/logo.png`).
     - `placeholderimagedefault`: Path to the default topic image (e.g., `/images/default.jpg`).
     - `maxrelatedpostsperpage`: Number of related topics (0, 1, 2, 3, 5, 7; default is 3).
5. **Update Templates**:
   - Open `/themes/{your_theme}/header.tpl` and ensure it contains:
     ```html
     <meta name="description" content="{HEADER_META_DESCRIPTION}" />
     <meta name="keywords" content="{HEADER_META_KEYWORDS}" />
     ```
     Also add, if missing:
     ```html
     <!-- IF {PHP.out.meta} -->{PHP.out.meta}<!-- ENDIF -->
     ```
   - Open `/themes/{your_theme}/forums.posts.tpl` and add:
     ```html
     <!-- IF {PHP|cot_plugin_active('seoforums')} -->
     <!-- BEGIN: RELATED_TOPICS -->
     <div class="mb-4 mt-5">
       <h3 class="h4 mt-3">{PHP.L.seoforums_related}</h3>
       <div class="list-group list-group-striped list-group-flush">
         <!-- BEGIN: RELATED_ROW -->
         <div class="list-group-item list-group-item-action">
           <a class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" href="{RELATED_TOPIC_ROW_URL}">
             <div class="row g-3">
               <div class="col-12 col-md-1 text-center">
                 <img src="{RELATED_TOPIC_ROW_IMAGE}" alt="{RELATED_TOPIC_ROW_TITLE}" class="rounded-circle" width="64" height="64">
               </div>
               <div class="col-12 col-md-11">
                 <h5 class="mb-0 fs-6 fw-semibold text-primary-emphasis">{RELATED_TOPIC_ROW_TITLE}</h5>
                 <!-- IF {RELATED_TOPIC_ROW_DESC} -->
                 <p class="mb-1 text-muted">{RELATED_TOPIC_ROW_DESC}</p>
                 <!-- ENDIF -->
                 <p class="mb-1 text-muted small">{RELATED_TOPIC_ROW_AUTHOR}</p>
               </div>
             </div>
           </a>
         </div>
         <!-- END: RELATED_ROW -->
       </div>
     </div>
     <!-- END: RELATED_TOPICS -->
     <!-- ENDIF -->
     ```
     Also in `forums.posts.tpl`, add for displaying reading time and topic author:
     ```html
     <!-- IF {PHP|cot_plugin_active('seoforums')} -->
     {TOPIC_READ_TIME}
     {TOPIC_AUTHOR}
     <!-- ENDIF -->
     ```
6. **Clear Cache**:
   - Go to **Administration → Cache → Clear Cache**.
7. **Test the Plugin**:
   - Visit a forum topic page, e.g., `https://abuyfile.com/index.php?e=forums&m=posts&q=171&l=ru`.
   - Verify:
     - `<meta name="description">` and `<meta name="keywords">` in `<head>`.
     - Open Graph and Schema.org meta tags via `{PHP.out.meta}`.
     - Reading time (`{TOPIC_READ_TIME}`), author (`{TOPIC_AUTHOR}`), and related topics block (`RELATED_TOPICS`).

## Recommendations
- **Image Configuration**:
  - Specify valid paths for `placeholderlogo` and `placeholderimagedefault` to ensure proper display in meta tags and related topics.
- **Stop Words**:
  - Expand the stop words list in `seoforums.ru.lang.php` or `seoforums.en.lang.php` to improve keyword extraction:
    ```php
    $L['seoforums_stop_words'] = 'a,an,the,and,but,or,for,of,in,to,at,by,from,...';
    ```
- **Database Optimization**:
  - Ensure indexes on `cot_forum_topics` (`ft_id`, `ft_cat`, `ft_firstposterid`), `cot_forum_posts` (`fp_topicid`), `cot_users` (`user_id`), and `cot_structure` (`structure_code`, `structure_area`) are created to speed up queries.
- **Testing**:
  - Use browser developer tools to check meta tags and JSON-LD.
  - Create multiple topics in one category to test the related topics block.

## Warnings
- **Template Conflicts**:
  - Ensure `{PHP.out.meta}` is not duplicated in `header.tpl` to avoid multiple Open Graph, Twitter Card, or Schema.org meta tags.
- **Images**:
  - If the `ft_image` field in the `cot_forum_topics` table is missing or empty, the plugin uses `placeholderlogo` or `placeholderimagedefault`. Specify valid images in the configuration.
- **Performance**:
  - Keyword extraction (`cot_extract_keywords_forums`) may slow down loading for long texts. Consider caching results.
- **Language Support**:
  - The plugin includes Russian (`seoforums.ru.lang.php`) and English (`seoforums.en.lang.php`) languages. Add corresponding files for other languages.

## License
BSD License. Author: webitproff, Copyright (c) 2025. Repository: https://github.com/webitproff/cot-seoforums.

## Support
**[Help and Discussion](https://abuyfile.com/index.php?e=forums&m=posts&q=171&l=ru)**

## Version
2.0.1 (Date: 2025-10-19).


# Плагин SEO Forum для Cotonti

Плагин **SEO Forum** расширяет SEO-возможности модуля форумов в [Cotonti CMF](https://github.com/Cotonti/Cotonti) версии 0.9.26 на PHP 8.4. Он добавляет мета-теги, Open Graph, Twitter Card, структурированные данные Schema.org, извлечение ключевых слов, оценку времени чтения и вывод похожих тем на страницах форума.

## Протестировано
Cotonti CMF v.0.9.26 на PHP v.8.4

## Структура файлов
```
plugins/seoforums/
├── inc/
│   └── seoforums.functions.php # Функции для извлечения ключевых слов и оценки времени чтения
├── lang/
│   ├── seoforums.ru.lang.php # Русские переводы и стоп-слова для извлечения ключевых слов
│   └── seoforums.en.lang.php # Английские переводы и стоп-слова для извлечения ключевых слов
├── seoforums.forums.posts.php # Добавляет мета-теги Open Graph, Twitter Card и Schema.org
├── seoforums.forums.tags.php # Определяет теги страницы (TOPIC_READ_TIME, TOPIC_AUTHOR, RELATED_TOPIC_ROW_*)
├── seoforums.header.tags.php # Переопределяет HEADER_META_DESCRIPTION и HEADER_META_KEYWORDS
└── seoforums.setup.php # Конфигурация и установка плагина
```

### Описание файлов
- **seoforums.setup.php**: Определяет метаданные плагина (название, версия, автор) и конфигурацию (пути к изображениям, количество похожих тем).
- **seoforums.functions.php**: Содержит функции:
  - `cot_extract_keywords_forums($text, $limit = 10)`: Извлекает ключевые слова из текста, удаляя HTML, приводя к нижнему регистру, исключая стоп-слова и сортируя по частоте.
  - `cot_estimate_read_time_forums($text)`: Оценивает время чтения, удаляя HTML/BB-коды, считая слова (200 слов/минута, минимум 1 минута).
- **seoforums.header.tags.php**: Переопределяет `{HEADER_META_DESCRIPTION}` и `{HEADER_META_KEYWORDS}` для мета-тегов на страницах тем форума.
- **seoforums.forums.posts.php**: Генерирует мета-теги Open Graph, Twitter Card и Schema.org для страниц тем, включая заголовок, описание, ключевые слова, автора, категорию и изображения.
- **seoforums.forums.tags.php**: Определяет теги для шаблона `forums.posts.tpl`, такие как `{TOPIC_READ_TIME}`, `{TOPIC_AUTHOR}`, `{RELATED_TOPIC_ROW_URL}`, `{RELATED_TOPIC_ROW_TITLE}`, `{RELATED_TOPIC_ROW_DESC}`, `{RELATED_TOPIC_ROW_IMAGE}`, `{RELATED_TOPIC_ROW_AUTHOR}`.
- **seoforums.ru.lang.php**: Содержит русские переводы ("Похожие темы", "мин чтения", "Неизвестный автор") и стоп-слова для извлечения ключевых слов.
- **seoforums.en.lang.php**: Содержит английские переводы (например, "Related topics", "min read", "Unknown author") и стоп-слова для извлечения ключевых слов.

## Возможности
- **Мета-теги**:
  - Генерирует `<meta name="description">` (категория + описание темы или текст первого поста, усечённое до 160 символов) и `<meta name="keywords">` (извлечённые ключевые слова).
  - Обеспечивает описание в одну строку без переносов.
- **Open Graph и Twitter Card**:
  - Добавляет теги `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name`, `og:locale`, `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image` для шаринга в соцсетях.
- **Schema.org**:
  - Включает структурированные данные для типа `DiscussionForumPosting` (`headline`, `description`, `keywords`, `author`, `publisher`, `image`, `datePublished`, `dateModified`, `mainEntityOfPage`).
- **Извлечение ключевых слов**:
  - Извлекает до 10 ключевых слов из текста первого поста с помощью `cot_extract_keywords_forums`, исключая стоп-слова из `seoforums.ru.lang.php` или `seoforums.en.lang.php`.
- **Оценка времени чтения**:
  - Рассчитывает время чтения первого поста с помощью `cot_estimate_read_time_forums` (200 слов/минута, минимум 1 минута).
- **Похожие темы**:
  - Отображает до 3 (настраиваемо) похожих тем из той же категории или других категорий, с заголовком, описанием, изображением (`ft_image` или плейсхолдер) и автором.
- **Автор темы**:
  - Показывает имя автора из `cot_users` по `ft_firstposterid` или "Неизвестный автор", если данные отсутствуют.

## Функциональность
- **Микроразметка**: Добавляет структурированные данные в формате JSON-LD для типа "DiscussionForumPosting", включая заголовок, описание, ключевые слова, автора, издателя, даты публикации и модификации, изображение и ссылку на страницу.
- **Open Graph и Twitter Card**: Генерирует мета-теги для социальных сетей, включая заголовок, описание, URL, изображение и тип контента.
- **Похожие темы**: Выводит блок с похожими темами (RELATED_TOPICS) на странице темы форума. Количество тем настраивается в конфигурации (по умолчанию 3). Темы берутся из той же категории, если возможно, иначе из других категорий. Для каждой похожей темы отображается URL, заголовок, описание (из ft_desc или первого поста), изображение (ft_image или плейсхолдер) и автор.
- **Время чтения**: Рассчитывает ориентировочное время чтения первого поста темы на основе количества слов (средняя скорость 200 слов в минуту, минимум 1 минута). Выводится в шаблоне как "X мин чтения".
- **Ключевые слова**: Извлекает ключевые слова из текста первого поста, исключая стоп-слова (список на русском), учитывая частоту и длину слов (>2 символов). Возвращает до 10 ключевых слов.
- **Автор темы**: Определяет автора по ft_firstposterid, если не найден — "Неизвестный автор".
- **Переопределение мета-тегов**: На страницах тем форума устанавливает meta description (категория + описание, усечённое до 160 символов) и meta keywords (извлечённые ключевые слова).
- **Изображения**: Использует плейсхолдеры для логотипа (маленькое изображение) и изображения темы (большое изображение), настраиваемые в конфигурации.

## Установка

### Требования
- Cotonti CMF v.0.9.26.
- Модуль `forums` включён.
- База данных MySQL/MariaDB.
- Рекомендуемые плагины: `attacher`, `urleditor`.

### Шаги
1. **Скачайте плагин**:
   - Клонируйте или скачайте репозиторий с GitHub:
     ```bash
     git clone https://github.com/webitproff/cot-seoforums.git
     ```
   - Или скачайте ZIP и распакуйте в `/plugins/`.
2. **Скопируйте файлы**:
   - Поместите папку `seoforums` в `/plugins/` вашей установки Cotonti:
     ```
     public_html/plugins/seoforums/
     ```
3. **Установите плагин**:
   - Перейдите в **Администрирование → Расширения** в админ-панели Cotonti.
   - Найдите **SEO Forum** в списке и нажмите **Установить**.
   - Плагин зарегистрируется с помощью `seoforums.setup.php`.
4. **Настройте конфигурацию**:
   - Перейдите в **Администрирование → Расширения → seoforums → Конфигурация**.
   - Укажите:
     - `placeholderlogo`: Путь к логотипу (например, `/images/logo.png`).
     - `placeholderimagedefault`: Путь к изображению темы (например, `/images/default.jpg`).
     - `maxrelatedpostsperpage`: Количество похожих тем (0, 1, 2, 3, 5, 7; по умолчанию 3).
5. **Обновите шаблоны**:
   - Откройте `/themes/{ваша_тема}/header.tpl` и убедитесь, что он содержит:
     ```html
     <meta name="description" content="{HEADER_META_DESCRIPTION}" />
     <meta name="keywords" content="{HEADER_META_KEYWORDS}" />
     ```
     Также добавьте, если отсутствует:
     ```html
     <!-- IF {PHP.out.meta} -->{PHP.out.meta}<!-- ENDIF -->
     ```
   - Откройте `/themes/{ваша_тема}/forums.posts.tpl` и добавьте:
     ```html
     <!-- IF {PHP|cot_plugin_active('seoforums')} -->
     <!-- BEGIN: RELATED_TOPICS -->
     <div class="mb-4 mt-5">
       <h3 class="h4 mt-3">{PHP.L.seoforums_related}</h3>
       <div class="list-group list-group-striped list-group-flush">
         <!-- BEGIN: RELATED_ROW -->
         <div class="list-group-item list-group-item-action">
           <a class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" href="{RELATED_TOPIC_ROW_URL}">
             <div class="row g-3">
               <div class="col-12 col-md-1 text-center">
                 <img src="{RELATED_TOPIC_ROW_IMAGE}" alt="{RELATED_TOPIC_ROW_TITLE}" class="rounded-circle" width="64" height="64">
               </div>
               <div class="col-12 col-md-11">
                 <h5 class="mb-0 fs-6 fw-semibold text-primary-emphasis">{RELATED_TOPIC_ROW_TITLE}</h5>
                 <!-- IF {RELATED_TOPIC_ROW_DESC} -->
                 <p class="mb-1 text-muted">{RELATED_TOPIC_ROW_DESC}</p>
                 <!-- ENDIF -->
                 <p class="mb-1 text-muted small">{RELATED_TOPIC_ROW_AUTHOR}</p>
               </div>
             </div>
           </a>
         </div>
         <!-- END: RELATED_ROW -->
       </div>
     </div>
     <!-- END: RELATED_TOPICS -->
     <!-- ENDIF -->
     ```
     Также в `forums.posts.tpl` добавьте для отображения времени чтения и автора темы:
     ```html
     <!-- IF {PHP|cot_plugin_active('seoforums')} -->
     {TOPIC_READ_TIME}
     {TOPIC_AUTHOR}
     <!-- ENDIF -->
     ```
6. **Очистите кэш**:
   - Перейдите в **Администрирование → Кэш → Очистить кэш**.
7. **Протестируйте плагин**:
   - Перейдите на страницу темы форума, например: (`https://abuyfile.com/index.php?e=forums&m=posts&q=171&l=ru`).
   - Проверьте:
     - `<meta name="description">` и `<meta name="keywords">` в `<head>`.
     - Мета-теги Open Graph и Schema.org через `{PHP.out.meta}`.
     - Время чтения (`{TOPIC_READ_TIME}`), автора (`{TOPIC_AUTHOR}`) и блок похожих тем (`RELATED_TOPICS`).

## Рекомендации
- **Конфигурация изображений**:
  - Укажите валидные пути в `placeholderlogo` и `placeholderimagedefault` для корректного отображения в мета-тегах и блоке похожих тем.
- **Стоп-слова**:
  - Расширьте список стоп-слов в `seoforums.ru.lang.php` или `seoforums.en.lang.php` для улучшения извлечения ключевых слов:
    ```php
    $L['seoforums_stop_words'] = 'а,без,более,бы,был,была,были,было,быть,в,вам,вас,весь,во,вот,...';
    ```
- **Оптимизация базы данных**:
  - Убедитесь, что индексы на `cot_forum_topics` (`ft_id`, `ft_cat`, `ft_firstposterid`), `cot_forum_posts` (`fp_topicid`), `cot_users` (`user_id`) и `cot_structure` (`structure_code`, `structure_area`) созданы для ускорения запросов.
- **Тестирование**:
  - Используйте инструменты разработчика браузера для проверки мета-тегов и JSON-LD.
  - Создайте несколько тем в одной категории для проверки блока похожих тем.

## Предупреждения
- **Конфликты шаблонов**:
  - Убедитесь, что `{HEADER_META_DESCRIPTION}`, `{HEADER_META_KEYWORDS}`, и `{PHP.out.meta}` не дублируются в `header.tpl`, чтобы избежать множественных мета-тегов.
- **Изображения**:
  - Если поле `ft_image` в таблице `cot_forum_topics` отсутствует или пустое, плагин использует `placeholderlogo` или `placeholderimagedefault`. Укажите валидные изображения в конфигурации.
- **Производительность**:
  - Извлечение ключевых слов (`cot_extract_keywords_forums`) может замедлить загрузку для длинных текстов. Рассмотрите кэширование результатов.
- **Языковая поддержка**:
  - Плагин включает русский (`seoforums.ru.lang.php`) и английский (`seoforums.en.lang.php`) языки. Для других языков добавьте соответствующие файлы.

## Лицензия
BSD License. Автор: webitproff, Copyright (c) 2025. Репозиторий: https://github.com/webitproff/cot-seoforums.
## Поддержка
**[помощь и обсуждение](https://abuyfile.com/index.php?e=forums&m=posts&q=171&l=ru)**
## Версия
2.0.1 (Дата: 2025-10-19).
