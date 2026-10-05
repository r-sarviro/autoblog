# AUTO-BLOG

Простой автомобильный блог на чистом PHP 8.1+, MySQL и Smarty без PHP-фреймворков.

## Возможности

- главная страница с категориями и 3 последними статьями;
- страница категории с сортировкой (дата / просмотры) и пагинацией;
- страница статьи со счётчиком просмотров и похожими материалами;
- связь статей и категорий many-to-many;
- локализация **ru / en** (UI + контент) с префиксом `/en`;
- базовое SEO (title/description, canonical, hreflang, Open Graph, Twitter Cards, JSON-LD, robots, sitemap);
- сидинг демо-данных;
- unit-тесты на PHPUnit.

## Требования

- PHP 8.1+
- MySQL 8+
- Composer
- Docker / Docker Compose (рекомендуемый способ запуска)

## Быстрый старт через Docker

```bash
cp .env.docker.example .env
composer install
docker compose up -d --build
docker compose exec app php database/seeders/seed.php
```

Сайт: [http://localhost:8080](http://localhost:8080)  
English: [http://localhost:8080/en/](http://localhost:8080/en/)

MySQL с хоста: `127.0.0.1:3307` (user/password: `auto_blog` / `secret`).

Если схема БД уже была создана со старой версией, пересоздайте volume:

```bash
docker compose down -v
docker compose up -d --build
docker compose exec app php database/seeders/seed.php
```

Остановка:

```bash
docker compose down
```

## Локальный запуск без Docker

1. Установите зависимости:

```bash
composer install
cp .env.example .env
```

2. Создайте БД и примените схему:

```bash
mysql -u root -p -e "CREATE DATABASE auto_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p auto_blog < database/schema/schema.sql
```

3. Заполните `.env` параметрами подключения и выполните сидинг:

```bash
php database/seeders/seed.php
# или
composer seed
```

4. Запустите встроенный сервер:

```bash
php -S localhost:8000 -t public
```

Откройте [http://localhost:8000](http://localhost:8000) и [http://localhost:8000/en/](http://localhost:8000/en/).

## URL

| Страница  | RU                                                    | EN                                                        |
| --------- | ----------------------------------------------------- | --------------------------------------------------------- |
| Главная   | `/`                                                   | `/en/`                                                    |
| Категория | `/category.php?slug=news&sort=date&page=1`            | `/en/category.php?slug=news&sort=date&page=1`             |
| Статья    | `/article.php?slug=russian-car-market-september-2025` | `/en/article.php?slug=russian-car-market-september-2025`  |
| robots    | `/robots.php` (в Docker также `/robots.txt`)          | `/en/robots.php`                                          |
| sitemap   | `/sitemap.php`                                        | `/en/sitemap.php` (тот же набор URL)                      |

Допустимые значения `sort`: `date` (по умолчанию), `views`.

Slug статей и категорий **общие** для обеих локалей; меняется префикс и текстовый контент.

## Локализация

- UI-строки: `lang/ru.php`, `lang/en.php` + `App\I18n\Translator`
- Контент: таблицы `category_translations`, `article_translations` (`locale` = `ru` | `en`)
- Если EN-перевод отсутствует — fallback на RU (`COALESCE`)
- Переключатель языка в шапке ведёт на ту же страницу в другой локали

## SEO

Базовые мета-теги собираются в `App\Support\SeoMeta` из локализованных полей (`title`, `description`, `image`):

- `<title>`, `<meta name="description">`, `canonical` с учётом локали;
- `hreflang` (`ru`, `en`, `x-default` → RU);
- Open Graph (`og:locale`, `og:locale:alternate`) и Twitter Cards;
- JSON-LD: `WebSite` / `CollectionPage` / `Article` + `BreadcrumbList`;
- 404/500 отдаются с `noindex,nofollow`;
- `sitemap.php` включает URL обеих локалей с xhtml alternates.

Для корректных canonical/OG/sitemap задайте в `.env` публичный адрес сайта (`APP_URL`).

## Тесты

```bash
composer test
# или
./vendor/bin/phpunit
```

## Структура проекта

```text
public/          HTTP entrypoints (в т.ч. public/en/) и assets
lang/            UI-переводы ru/en
src/             Config, Database, I18n, Repository, Service, Support, View
templates/       Smarty layouts и partials
database/        schema.sql и seeders
tests/Unit/      PHPUnit
docker/          PHP и Nginx конфиги
```

## Контент и безопасность

- текст статей хранится как plain text в таблицах переводов;
- в шаблонах вывод идёт через escape Smarty;
- SQL только через PDO prepared statements;
- сортировка только через whitelist колонок;
- `.env` не коммитится (есть `.env.example` и `.env.docker.example`).

## Чеклист проверки

- [ ] Главная показывает только категории со статьями
- [ ] У каждой категории до 3 последних статей и кнопка «Все статьи»
- [ ] Сортировка по дате и просмотрам работает
- [ ] Пагинация работает (в «Новости» 12 статей → 2 страницы)
- [ ] Открытие статьи увеличивает `views`
- [ ] Блок похожих статей не содержит текущую
- [ ] Несуществующие slug возвращают 404
- [ ] Некорректные `page` / `sort` не ломают страницу
- [ ] В `<head>` есть description, canonical, hreflang, Open Graph и JSON-LD
- [ ] `/en/` отдаёт английский UI и контент
- [ ] Переключатель RU/EN сохраняет текущую страницу
- [ ] `/robots.php` и `/sitemap.php` отдают корректные ответы
- [ ] `composer test` проходит успешно
