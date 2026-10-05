# Демо-данные AUTO-BLOG

Набор моковых данных по модели из `blog_requirements.md`.

## Файлы

| Файл | Назначение |
|------|------------|
| `seed_data.json` | Основной источник: категории, статьи, `category_ids` |
| `seed_data.sql` | Готовый SQL-импорт (TRUNCATE + INSERT) |
| `article_category.json` | Отдельная таблица связей many-to-many |
| `../schema/schema.sql` | DDL таблиц `categories`, `articles`, `article_category` |

## Состав данных

- **6 категорий** (у «Архив» нет статей — для проверки главной)
- **100 статей** с разными `published_at` и `views`
- **140 связей** article ↔ category
- Много статей в 2 категориях сразу
- Пагинация: Новости 36, Советы 33, Обзоры 26, Технологии 25, Электромобили 20
- **`content`** — развёрнутый plain text (~15 абзацев); в Smarty: `{$article.content|escape|nl2br}`

## Импорт через MySQL

```bash
mysql -u USER -p DATABASE < database/schema/schema.sql
mysql -u USER -p DATABASE < database/seeders/data/seed_data.sql
```

## Импорт через PHP seed (рекомендуется)

```php
<?php
$data = json_decode(
    file_get_contents(__DIR__ . '/data/seed_data.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

foreach ($data['categories'] as $category) {
    // INSERT INTO categories ...
}

foreach ($data['articles'] as $article) {
    // INSERT INTO articles ... (без category_ids)
    foreach ($article['category_ids'] as $categoryId) {
        // INSERT INTO article_category (article_id, category_id)
    }
}
```

## Изображения

Все 100 изображений лежат в:

```text
public/assets/images/articles/
```

- Формат: JPEG 16:9 (~1280px)
- Единый стиль: photorealistic automotive editorial, cool blue-steel color grade
- Сюжеты подобраны под темы статей из `seed_data.json`
- Пути: `/assets/images/articles/*.jpg`
