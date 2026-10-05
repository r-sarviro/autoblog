# AUTO-BLOG

Простой автомобильный блог на чистом PHP 8.1+, MySQL и Smarty без PHP-фреймворков.

## Возможности

- главная страница с категориями и 3 последними статьями;
- страница категории с сортировкой (дата / просмотры) и пагинацией;
- страница статьи со счётчиком просмотров и похожими материалами;
- связь статей и категорий many-to-many;
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

MySQL с хоста: `127.0.0.1:3307` (user/password: `auto_blog` / `secret`).

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

Откройте [http://localhost:8000](http://localhost:8000).

## URL

| Страница  | Пример                                                |
| --------- | ----------------------------------------------------- |
| Главная   | `/`                                                   |
| Категория | `/category.php?slug=news&sort=date&page=1`            |
| Статья    | `/article.php?slug=russian-car-market-september-2025` |

Допустимые значения `sort`: `date` (по умолчанию), `views`.

## Тесты

```bash
composer test
# или
./vendor/bin/phpunit
```

## Структура проекта

```text
public/          HTTP entrypoints и assets
src/             Config, Database, Repository, Service, Support, View
templates/       Smarty layouts и partials
database/        schema.sql и seeders
tests/Unit/      PHPUnit
docker/          PHP и Nginx конфиги
```

## Контент и безопасность

- текст статей хранится как plain text;
- в шаблонах вывод идёт через escape Smarty;
- тело статьи: `{$article.content|escape|nl2br nofilter}`;
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
- [ ] `composer test` проходит успешно
