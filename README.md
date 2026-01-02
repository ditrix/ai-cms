# AI CMS

Система управления клиентами с разделением доступа по ролям.

## Описание

AI CMS — это веб-приложение для управления клиентами и менеджерами. Система предоставляет различные уровни доступа в зависимости от роли пользователя:

- **Менеджер**: работает только со своими клиентами
- **Супер-менеджер**: управляет всеми клиентами и менеджерами
- **Администратор**: полный доступ ко всем разделам системы

## Технологический стек

### Backend
- PHP 8.2.29
- Laravel 12.40.2
- SQLite (для разработки)
- Laravel Fortify (аутентификация)

### Frontend
- Vue.js 3.5.22
- Inertia.js 2.0.11
- Tailwind CSS 4.1.14
- TypeScript (через Wayfinder)

### Инструменты разработки
- Pest 3.8.4 (тестирование)
- Laravel Pint 1.26.0 (форматирование кода)
- Laravel Sail 1.48.1 (Docker окружение)

## Требования

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- npm или yarn
- SQLite (для разработки) или MySQL/PostgreSQL (для production)

## Установка

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd ai-cms
```

### 2. Установка зависимостей

```bash
# Backend зависимости
composer install

# Frontend зависимости
npm install
```

### 3. Настройка окружения

```bash
# Копирование файла окружения
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate
```

Отредактируйте файл `.env` и настройте:
- `DB_CONNECTION` (sqlite для разработки)
- `DB_DATABASE` (путь к файлу базы данных для SQLite)
- Другие необходимые параметры

### 4. Настройка базы данных

```bash
# Выполнение миграций
php artisan migrate

# Создание администратора (опционально)
php artisan db:seed
```

### 5. Сборка фронтенда

```bash
# Для разработки (с hot reload)
npm run dev

# Для production
npm run build
```

### 6. Генерация Wayfinder типов

```bash
php artisan wayfinder:generate
```

### 7. Запуск приложения

```bash
# Запуск встроенного сервера Laravel
php artisan serve

# Или через Sail (Docker)
./vendor/bin/sail up
```

Приложение будет доступно по адресу: `http://localhost:8000`

## Использование

### Создание первого администратора

После установки создайте первого администратора через tinker:

```bash
php artisan tinker
```

```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => \App\Enums\UserRole::ADMIN,
    'is_active' => true,
    'email_verified_at' => now(),
]);
```

### Вход в систему

1. Откройте главную страницу `/`
2. Введите email и пароль
3. После успешной авторизации вы будете перенаправлены на `/dashboard`

### Роли и права доступа

#### Менеджер
- Просмотр и управление только своими клиентами
- Доступ к разделам: Главная, Клиенты

#### Супер-менеджер
- Просмотр и управление всеми клиентами
- Управление менеджерами (создание, редактирование, удаление)
- Смена ответственного менеджера у клиента
- Смена пароля менеджеров
- Управление активностью менеджеров
- Доступ к разделам: Главная, Клиенты, Менеджеры

#### Администратор
- Полный доступ ко всем разделам системы
- Все возможности супер-менеджера
- Может управлять менеджерами любого типа

## Разработка

### Структура проекта

```
ai-cms/
├── app/                    # Backend код
│   ├── Enums/             # Enum классы
│   ├── Http/              # Контроллеры, Requests, Middleware
│   ├── Models/            # Eloquent модели
│   └── Policies/          # Политики авторизации
├── database/              # Миграции, фабрики, сидеры
├── resources/             # Frontend ресурсы
│   ├── js/
│   │   ├── components/   # Vue компоненты
│   │   ├── pages/        # Inertia страницы
│   │   └── types/        # TypeScript типы
│   └── css/              # Стили
├── routes/                # Маршруты
├── tests/                 # Тесты
└── public/               # Публичные файлы
```

### Запуск тестов

```bash
# Все тесты
php artisan test

# Конкретный тест
php artisan test tests/Feature/ClientControllerTest.php

# С фильтром
php artisan test --filter=testName
```

### Форматирование кода

```bash
# PHP (Laravel Pint)
vendor/bin/pint

# JavaScript/TypeScript (Prettier)
npm run format
```

### Генерация Wayfinder типов

После изменения маршрутов необходимо перегенерировать типы:

```bash
php artisan wayfinder:generate
```

## API маршруты

### Клиенты

- `GET /dashboard/clients` - список клиентов
- `POST /dashboard/clients` - создание клиента
- `GET /dashboard/clients/{id}` - просмотр клиента
- `PUT /dashboard/clients/{id}` - обновление клиента
- `DELETE /dashboard/clients/{id}` - удаление клиента
- `POST /dashboard/clients/{id}/change-manager` - смена менеджера

### Менеджеры

- `GET /dashboard/managers` - список менеджеров
- `POST /dashboard/managers` - создание менеджера
- `GET /dashboard/managers/{id}` - просмотр менеджера
- `PUT /dashboard/managers/{id}` - обновление менеджера
- `DELETE /dashboard/managers/{id}` - удаление менеджера
- `POST /dashboard/managers/{id}/change-password` - смена пароля
- `POST /dashboard/managers/{id}/toggle-active` - переключение активности

## Документация

- [Документ по разработке](DEVELOPMENT.md) - подробное описание архитектуры и реализации
- [Документ по мануальному тестированию](MANUAL_TESTING.md) - инструкции по тестированию системы
- [Документ о процессе Vibe Coding](VIBE_CODING.md) - описание workflow в данном проекте

## Лицензия

Проект создан для исследовательских целей.

## Авторы

Проект разработан с использованием AI-ассистентов для исследования возможностей веб-разработки.

## Поддержка

При возникновении проблем или вопросов, пожалуйста, создайте issue в репозитории проекта.

