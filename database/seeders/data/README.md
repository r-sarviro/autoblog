# Демо-данные AUTO-BLOG

Набор моковых данных по модели блога с локалями `ru` / `en`.

## Файлы

| Файл | Назначение |
|------|------------|
| `seed_data.json` | Основной источник: категории, статьи, переводы, `category_ids` |
| `article_category.json` | Отдельная таблица связей many-to-many |
| `../schema/schema.sql` | DDL: `categories`, `articles`, `*_translations`, `article_category` |

## Состав данных

- **6 категорий** (у «Архив» / Archive нет статей — для проверки главной)
- **100 статей** с разными `published_at` и `views`
- **Переводы** `ru` и `en` для всех категорий и статей (`translations`)
- **Общие slug** для обеих локалей; язык выбирается префиксом `/en`
- **140 связей** article ↔ category
- Пагинация: Новости 36, Советы 33, Обзоры 26, Технологии 25, Электромобили 20
- **`content`** — plain text из 5 связных абзацев (`\n\n`)

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

Все 100 изображений лежат в:

```text
public/assets/images/articles/
```

- Формат: JPEG 16:9 (~1280px)
- Пути: `/assets/images/articles/*.jpg`
