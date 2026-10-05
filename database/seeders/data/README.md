# Демо-данные AUTO-BLOG

Набор моковых данных по модели блога с локалями `ru` / `en`.

## Файлы

| Файл | Назначение |
|------|------------|
| `seed_data.json` | Основной источник: категории, статьи, переводы, `category_ids` |
| `article_category.json` | Отдельная таблица связей many-to-many |
| `../schema/schema.sql` | DDL: `categories`, `articles`, `*_translations`, `article_category` |

## Состав данных

- **6 категорий** (у Archive / «Архив» нет статей — для проверки главной)
- **200 статей** с разными `published_at` и `views`
- **Переводы** `ru` и `en` для всех категорий и статей (`translations`)
- **Общие slug** для обеих локалей; язык выбирается префиксом `/en`
- **273 связи** article ↔ category
- Пагинация по категориям: tips 69, news 59, technology 50, reviews 49, electric 46
- **`content`** — plain text из 5 связных абзацев (`\n\n`)
- Статьи **101–200** используют общий плейсхолдер изображения

## Импорт через PHP seed (рекомендуется)

```bash
composer seed
# или
php database/seeders/seed.php
```

После смены схемы в Docker пересоздайте volume БД:

```bash
docker compose down -v
docker compose up -d --build
docker compose exec app php database/seeders/seed.php
```

## Изображения

Все 200 обложек лежат в `public/assets/images/articles/` (JPEG ~1280px, единый editorial-стиль).
