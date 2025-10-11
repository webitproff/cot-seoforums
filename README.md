# cot-seoforums
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
   - Перейдите на страницу темы форума (`index.php?r=forums&m=posts&q=ID`).
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

## Версия
2.0.1 (Дата: 2025-10-19).
