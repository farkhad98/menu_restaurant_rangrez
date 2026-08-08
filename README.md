# Rangrez Restaurant Menu

Двуязычное меню ресторана с публичной клиентской страницей и закрытой панелью управления.

В проекте используются две основные сущности:

- `categories` — категории на русском и английском языках;
- `products` — блюда, описание, цена, вес, изображение и категория.

Таблица `users` используется только для входа в панель управления. Публичной регистрации на сайте нет.

## Использованные технологии

### Серверная часть

- PHP 8.2;
- Laravel 12;
- MySQL 8;
- Eloquent ORM, миграции и сидеры Laravel;
- Blade для панели управления;
- сессии Laravel для авторизации администратора;
- PHPUnit 11 для автоматических тестов.

### Клиентская часть

- Vue 3;
- Vue Router 4;
- Vuex 4;
- Axios;
- Vite 8;
- адаптивные HTML и CSS;
- Bootstrap 5.2 и Feather Icons в панели управления.

### Изображения блюд

- поддерживаются JPG, PNG, WebP и AVIF размером до 25 МБ;
- исходная фотография может иметь размер до 60 мегапикселей, но не более 12000 пикселей по одной стороне;
- после загрузки изображение автоматически уменьшается до 2400 пикселей по длинной стороне и сохраняется в WebP;
- для корректной работы WebP и AVIF в PHP должны быть включены расширения `gd` и `exif` (в настроенном PHP 8.2 OSPanel они уже включены).

### Локальная среда и инфраструктура

- OSPanel с Apache, PHP 8.2 и MySQL 8;
- Composer для PHP-зависимостей;
- Node.js и npm для клиентской сборки;
- Docker Compose, Nginx и PHP-FPM как дополнительный вариант запуска.

## Требования

- PHP 8.2 или новее;
- Composer 2;
- MySQL 8;
- Node.js 20.19+ или 22.12+;
- npm 10 или новее.

## Первый запуск через OSPanel

### 1. Настройка модулей

В настройках OSPanel выберите:

- HTTP-сервер: Apache 2.4;
- PHP: 8.2;
- база данных: MySQL 8.

Папка проекта:

```text
D:\OSPanel\domains\rengrezMenuApp
```

Корневая директория домена должна указывать на папку `public` проекта:

```text
D:\OSPanel\domains\rengrezMenuApp\public
```

Для текущей настройки используется домен:

```text
http://rangrez
```

### 2. Создание базы данных

Создайте пустую базу данных MySQL с именем `rangrez`. Это можно сделать через phpMyAdmin, Adminer или консоль MySQL.

### 3. Настройка `.env`

Если файла `.env` ещё нет, создайте его из примера:

```powershell
Copy-Item .env.example .env
```

Укажите настройки приложения и базы данных:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://rangrez
APP_TIMEZONE=Asia/Tashkent

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rangrez
DB_USERNAME=root
DB_PASSWORD=
```

Для создания администратора также заполните:

```env
ADMIN_NAME=Administrator
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=сложный_пароль
```

Не добавляйте настоящий `.env` в Git и не публикуйте пароль администратора.

### 4. Установка зависимостей

Откройте консоль OSPanel и выполните:

```powershell
Set-Location D:\OSPanel\domains\rengrezMenuApp
composer install
npm install
```

### 5. Подготовка Laravel и базы данных

```powershell
php artisan key:generate
php artisan migrate --seed
```

Команда создаст таблицы, тестовое меню и администратора из переменных `ADMIN_*`.

Тестовое меню содержит 8 категорий и 40 блюд на двух языках. Для проверки загрузчика сидер создаёт исходные изображения в JPG, PNG, WebP и AVIF разных разрешений, пропускает их через обработчик приложения и сохраняет оптимизированные WebP-файлы.

Чтобы повторно добавить или восстановить только тестовое меню без очистки существующей базы:

```powershell
php artisan db:seed --class=DemoMenuSeeder
```

Если нужно только создать или обновить администратора:

```powershell
php artisan db:seed --class=AdminSeeder
```

### 6. Сборка клиентской части

Однократная production-сборка:

```powershell
npm run build
```

Автоматическая пересборка во время разработки:

```powershell
npm run dev
```

Команда `npm run dev` в этом проекте запускает Vite в режиме наблюдения. Сам сайт продолжает обслуживаться через OSPanel.

### 7. Запуск

Запустите OSPanel и откройте:

- русское меню: `http://rangrez/ru`;
- английское меню: `http://rangrez/en`;
- вход в админку: `http://rangrez/dashboard/login`.

## Ежедневный запуск

Если проект уже установлен:

1. Запустите OSPanel.
2. Убедитесь, что Apache и MySQL работают.
3. Откройте `http://rangrez/ru`.
4. При изменении Vue или CSS запустите `npm run dev`.

Повторно выполнять миграции и устанавливать зависимости при каждом запуске не нужно.

## Проверка проекта

Запуск PHP-тестов:

```powershell
php artisan test
```

Проверка production-сборки:

```powershell
npm run build
```

Просмотр состояния миграций:

```powershell
php artisan migrate:status
```

## Production-запуск через Docker

Создайте отдельный production-файл настроек:

```powershell
Copy-Item .env.production.example .env.production
php artisan key:generate --show
```

Вставьте полученный ключ в `APP_KEY` файла `.env.production`. Также обязательно замените `APP_URL`, `DOCKER_DB_PASSWORD`, `DOCKER_DB_ROOT_PASSWORD`, `ADMIN_EMAIL` и `ADMIN_PASSWORD`.

Подготовьте PHP-зависимости и клиентскую сборку:

```powershell
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Соберите и запустите контейнеры:

```powershell
docker compose --env-file .env.production up -d --build
docker compose --env-file .env.production exec php php artisan migrate --force
docker compose --env-file .env.production exec php php artisan db:seed --class=AdminSeeder --force
docker compose --env-file .env.production exec php php artisan optimize
```

Приведённая production-команда создаёт только администратора и не добавляет демонстрационное меню. Тестовые данные запускаются отдельно только при необходимости.

Контейнер Nginx слушает порт из `APP_PORT` (по умолчанию `8080`). На сервере перед ним должен находиться HTTPS reverse proxy; значение `APP_URL` должно начинаться с `https://`. Порт MySQL наружу не публикуется.

Для локальной проверки Docker без HTTPS временно задайте `SESSION_SECURE_COOKIE=false`. На production это значение должно оставаться `true`.

## Основные файлы проекта

- `app/Models/Category.php` и `app/Models/Product.php` — модели данных;
- `app/Http/Controllers/HomeController.php` — API публичного меню;
- `app/Http/Controllers/Dashboard` — управление категориями, продуктами и входом;
- `database/migrations` — структура базы данных;
- `database/seeders` — начальные данные и создание администратора;
- `resources/js/components/MenuComponent.vue` — карточки, фильтры и модальное окно;
- `resources/css/app.css` — стили публичной страницы;
- `resources/views/dashboard` — шаблоны панели управления;
- `public/css/dashboard.css` — адаптивные стили панели управления.

## Полезные команды при проблемах

Очистка кешей Laravel:

```powershell
php artisan optimize:clear
```

Повторная сборка интерфейса:

```powershell
npm run build
```

Если изменились настройки `.env`, после этого выполните `php artisan optimize:clear`.
