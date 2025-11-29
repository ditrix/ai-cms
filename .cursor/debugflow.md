# Debug Flow Document — AI CMS

## Проблема: Неправильная роль у пользователя admin@mail.com

**Дата обнаружения**: 2025-01-27

### Описание проблемы

При старте приложения создана запись пользователя:
- **Email**: `admin@mail.com`
- **Password**: `password`

Но при входе в систему обнаружено, что права у пользователя как у менеджера:
- ❌ Нет списка менеджеров в навигации
- ✅ Видны только клиенты

### Диагностика

**Проверка базы данных:**
```sql
SELECT id, name, email, role, is_active FROM users WHERE email = 'admin@mail.com';
```

**Результат:**
- `id`: 3
- `name`: admin
- `email`: admin@mail.com
- `role`: **manager** ❌ (должно быть `admin`)
- `is_active`: 1

### Причина проблемы

Пользователь был создан без явного указания роли `admin`, поэтому была установлена роль по умолчанию `manager` (из миграции `2025_11_29_134914_add_role_and_is_active_to_users_table.php`).

### Решение

1. Обновить роль пользователя `admin@mail.com` на `admin` в базе данных
2. Проверить, где создается этот пользователь (DatabaseSeeder или другой seeder)
3. Исправить код создания пользователя, чтобы роль `admin` устанавливалась явно

---

## Выполнение задачи

**Задача**: `task-debug-1-admin-role-fix.mdc`

### Шаг 1: Обновление роли в базе данных ✅

Обновлена роль пользователя `admin@mail.com` с `manager` на `admin` через tinker:
```php
$admin = User::where('email', 'admin@mail.com')->first();
$admin->role = UserRole::ADMIN;
$admin->save();
```

**Результат проверки:**
- `role`: `admin` ✅
- `isAdmin()`: `true` ✅
- `isSuperManager()`: `false` ✅
- `isManager()`: `false` ✅

### Шаг 2: Проверка DatabaseSeeder ✅

Проверен файл `database/seeders/DatabaseSeeder.php` - пользователь `admin@mail.com` там не создавался.

### Шаг 3: Обновление DatabaseSeeder ✅

Обновлен `DatabaseSeeder` для создания пользователя `admin@mail.com` с ролью `admin` при выполнении `php artisan db:seed`:

```php
// Создание администратора
User::factory()->admin()->create([
    'name' => 'Admin',
    'email' => 'admin@mail.com',
]);
```

Теперь при выполнении `php artisan db:seed` будет создаваться администратор с правильной ролью.

---

## Результат

✅ **Проблема решена**

Пользователь `admin@mail.com` теперь имеет роль `admin` и может:
- ✅ Видеть раздел "Менеджеры" в навигации
- ✅ Управлять менеджерами (создание, редактирование, удаление)
- ✅ Управлять клиентами
- ✅ Выполнять все административные функции

### Проверка методов роли:
- `isAdmin()`: `true` ✅
- `isSuperManager()`: `false` ✅
- `isManager()`: `false` ✅

### Изменения в коде:
1. Обновлена роль пользователя в базе данных через tinker
2. Обновлен `DatabaseSeeder` для создания администратора с правильной ролью при выполнении `php artisan db:seed`

---

## Дополнительные задачи

### Задача: Создание CreateAdminSeeder и проверка прав админа

**Дата**: 2025-01-27

#### Требования:
1. Создать отдельный сидер `CreateAdminSeeder` с возможностью настройки имени, email и пароля
2. Админ должен создаваться через этот сидер
3. Админ должен иметь право менять роль менеджеру в админке
4. Создать админа с email "dmitry@mail.com" и паролем "password"

#### Выполненные действия:

1. ✅ **Создан CreateAdminSeeder** (`database/seeders/CreateAdminSeeder.php`)
   - Сидер поддерживает настройку параметров через методы `setName()`, `setEmail()`, `setPassword()`
   - Метод `setParameters()` для установки всех параметров сразу
   - Сидер проверяет существование администратора и обновляет его при необходимости
   - По умолчанию создает администратора с email `admin@mail.com`

2. ✅ **Обновлен DatabaseSeeder**
   - Использует `CreateAdminSeeder` для создания администраторов
   - Создает администратора `admin@mail.com` через сидер
   - Создает администратора `dmitry@mail.com` с указанными параметрами

3. ✅ **Создан администратор dmitry@mail.com**
   - Email: `dmitry@mail.com`
   - Пароль: `password`
   - Роль: `admin` ✅

4. ✅ **Проверены права админа на изменение роли менеджера**
   - Policy `UserPolicy::update()` разрешает админу обновлять менеджеров ✅
   - `UpdateManagerRequest` позволяет менять роль между `manager` и `super_manager` ✅
   - Админ может менять роль менеджеру через интерфейс ✅

#### Результат проверки прав:
```php
$admin = User::where('email', 'dmitry@mail.com')->first();
$manager = User::factory()->manager()->create();
Gate::forUser($admin)->allows('update', $manager); // true ✅
```

#### Администраторы в системе:
- `admin@mail.com` - роль: `admin` ✅
- `dmitry@mail.com` - роль: `admin` ✅

#### Использование CreateAdminSeeder:

```php
// В DatabaseSeeder или другом сидере
$seeder = new CreateAdminSeeder;
$seeder->setParameters('Имя', 'email@example.com', 'password');
$seeder->setCommand($this->command);
$seeder->run();
```

Или через методы:
```php
$seeder = new CreateAdminSeeder;
$seeder->setName('Имя')
       ->setEmail('email@example.com')
       ->setPassword('password');
$seeder->setCommand($this->command);
$seeder->run();
```

---

## Проблема: Роль Admin не отображается корректно на фронтенде

**Дата обнаружения**: 2025-01-27

### Описание проблемы

После запуска сидеров роль администратора в базе данных установлена корректно (`admin`), но на странице списка менеджеров отображается как `Manager` вместо `Admin`.

### Диагностика

**Проверка базы данных:**
- `admin@mail.com` - роль: `admin` ✅
- `dmitry@mail.com` - роль: `admin` ✅

**Проблема в коде:**
В файле `resources/js/pages/Managers/Index.vue` на строке 83 логика отображения роли проверяла только `super_manager`, а все остальные роли (включая `admin`) отображались как `Manager`:

```javascript
role: manager.role === 'super_manager' ? 'Super Manager' : 'Manager',
```

### Решение

Обновлена логика отображения роли в `Managers/Index.vue` для корректной обработки роли `admin`:

```javascript
let roleLabel = 'Manager';
if (manager.role === 'super_manager') {
    roleLabel = 'Super Manager';
} else if (manager.role === 'admin') {
    roleLabel = 'Admin';
}
```

### Результат

✅ **Проблема решена**

Теперь роли отображаются корректно:
- `manager` → `Manager`
- `super_manager` → `Super Manager`
- `admin` → `Admin`

---

## Проблема: Не работает функционал "Change Manager"

**Дата обнаружения**: 2025-11-29

### Описание проблемы

Функционал смены менеджера для клиента не работает. При попытке изменить менеджера через модальное окно форма не отправляет данные корректно.

### Диагностика

**Проверка маршрута:**
- Маршрут существует: `POST dashboard/clients/{client}/change-manager` ✅
- Контроллер: `ClientController@changeManager` ✅

**Проверка контроллера:**
- Метод `changeManager` существует ✅
- Проверка роли исправлена (используется `->value` для сравнения enum) ✅
- Валидация `manager_id` настроена корректно ✅

**Проблема в коде:**
В компоненте `ChangeManagerModal.vue` select элемент не использовал `v-model` для двусторонней привязки данных. Компонент `<Form>` из Inertia автоматически собирает данные из формы, но для корректной работы с Vue реактивностью необходим `v-model`.

### Решение

Обновлен компонент `ChangeManagerModal.vue`:

1. **Добавлен `ref` для хранения выбранного менеджера:**
```typescript
const selectedManagerId = ref<number | string>(props.client.manager_id || '');
```

2. **Добавлен `v-model` для select элемента:**
```vue
<select
    id="manager_id"
    name="manager_id"
    v-model="selectedManagerId"
    required
    ...
>
```

3. **Удален атрибут `:selected`** (управление выбором теперь через `v-model`)

### Результат

✅ **Проблема решена**

Теперь функционал "Change Manager" работает корректно:
- Select элемент правильно привязан к данным через `v-model`
- Форма корректно отправляет выбранное значение `manager_id` на сервер
- Менеджер клиента успешно изменяется

### Измененные файлы:
- `resources/js/components/ChangeManagerModal.vue` - добавлен `v-model` для select элемента
- `app/Http/Controllers/ClientController.php` - исправлена проверка роли (используется `->value`)

---

## Сводка всех исправлений

### 1. Неправильная роль у пользователя admin@mail.com ✅
- **Проблема**: Пользователь создавался с ролью `manager` вместо `admin`
- **Решение**: Обновлен `DatabaseSeeder` для явного указания роли `admin`

### 2. Создание CreateAdminSeeder ✅
- **Задача**: Создать отдельный сидер для создания администраторов
- **Решение**: Создан `CreateAdminSeeder` с возможностью настройки параметров

### 3. Отображение роли Admin на фронтенде ✅
- **Проблема**: Роль `admin` отображалась как `Manager`
- **Решение**: Обновлена логика отображения роли в `Managers/Index.vue`

### 4. Не работает функционал "Change Manager" ✅
- **Проблема**: Форма не отправляла данные корректно
- **Решение**: Добавлен `v-model` для select элемента в `ChangeManagerModal.vue`

---

**Последнее обновление**: 2025-11-29  
**Статус**: ✅ Все задачи выполнены

