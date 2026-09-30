# Task API Backend

Laravel 13 দিয়ে তৈরি একটি RESTful Task Management API। Laravel Sanctum দিয়ে token-based authentication করা হয়েছে। যেকোনো frontend (Vue, React, Next.js, Mobile App) থেকে এই API ব্যবহার করা যাবে।

---

## Table of Contents

1. [Features](#features)
2. [Requirements](#requirements)
3. [Technology](#technology)
4. [GitHub থেকে Download ও Run করা](#github-থেকে-download-ও-run-করা)
5. [API Endpoints](#api-endpoints)
6. [Frontend এ API ব্যবহার (Step by Step)](#frontend-এ-api-ব্যবহার-step-by-step)
7. [CORS Setup](#cors-setup)
8. [Testing](#testing)
9. [Common Problems](#common-problems)

---

## Features

- User Register / Login / Logout (Sanctum token)
- Public Task list ও single Task দেখা (login ছাড়া)
- Authenticated user এর জন্য Task CRUD (Create, Read, Update, Delete)
- Pest দিয়ে testing

---

## Requirements

| Software | Version |
|---|---|
| PHP | `^8.3` |
| Composer | `2.x` |
| Laravel | `^13.0` |
| Database | SQLite (default) / MySQL 8+ / PostgreSQL |
| Node.js + npm | `18+` (optional, শুধু `composer run setup` এর build step এর জন্য) |
| Git | latest |

**Required PHP extensions:** `mbstring`, `openssl`, `pdo`, `pdo_mysql` (MySQL ব্যবহার করলে) / `pdo_sqlite`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`

---

## Technology

- **Backend Framework:** Laravel 13
- **Language:** PHP 8.3+
- **Authentication:** Laravel Sanctum 4
- **Dev Tools:** Laravel Tinker, Pail, Pint, Boost
- **Testing:** Pest 4 + Pest Laravel Plugin
- **Database:** SQLite / MySQL

---

## GitHub থেকে Download ও Run করা

### Step 1: Repository clone করো

```bash
git clone https://github.com/hassanroki/task_api_backend.git
cd task_api_backend
```

### Step 2: PHP dependencies install করো

```bash
composer install
```

### Step 3: `.env` file বানাও

```bash
cp .env.example .env
```

Windows (CMD) হলে:

```bash
copy .env.example .env
```

### Step 4: Application key generate করো

```bash
php artisan key:generate
```

### Step 5: Database configure করো

**Option A: SQLite (সবচেয়ে সহজ)**

```bash
touch database/database.sqlite
```

`.env` এ:

```env
DB_CONNECTION=sqlite
```

**Option B: MySQL**

MySQL এ একটি database বানাও (যেমন `task_api`), তারপর `.env` এ:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=
```

### Step 6: Migration run করো

```bash
php artisan migrate
```

### Step 7: Server চালু করো

```bash
php artisan serve
```

API এখন চলবে: `http://127.0.0.1:8000`
API base URL: `http://127.0.0.1:8000/api`

> **Shortcut:** উপরের Step 2–6 একসাথে করতে চাইলে `composer run setup` চালাতে পারো।

---

## API Endpoints

Base URL: `http://127.0.0.1:8000/api`

### Public Routes (token লাগবে না)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/register` | নতুন user register |
| POST | `/login` | Login করে token নেওয়া |
| GET | `/task-list` | সব task এর list |
| GET | `/task-list/{id}` | একটি task এর details |

### Protected Routes (token লাগবে)

Header এ পাঠাতে হবে: `Authorization: Bearer YOUR_TOKEN`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/user` | Logged in user এর তথ্য |
| POST | `/logout` | Logout (token বাতিল) |
| GET | `/tasks` | Task list |
| POST | `/tasks` | নতুন task তৈরি |
| GET | `/tasks/{id}` | একটি task দেখা |
| PUT / PATCH | `/tasks/{id}` | Task update |
| DELETE | `/tasks/{id}` | Task delete |

### Request Body Example

> নিচের field নামগুলো example। তোমার `AuthController` ও `TaskController` এর validation অনুযায়ী মিলিয়ে নিও।

**Register**

```json
{
  "name": "Hassan",
  "email": "hassan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Login**

```json
{
  "email": "hassan@example.com",
  "password": "password123"
}
```

**Create Task**

```json
{
  "title": "Learn Laravel",
  "description": "Sanctum API complete করতে হবে",
  "status": "pending"
}
```

### Postman / cURL দিয়ে Test

```bash
# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"hassan@example.com","password":"password123"}'

# Task create (token সহ)
curl -X POST http://127.0.0.1:8000/api/tasks \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"title":"My first task"}'
```

> সবসময় `Accept: application/json` header দিবে, নাহলে error এ JSON না এসে HTML redirect আসতে পারে।

---

## Frontend এ API ব্যবহার (Step by Step)

উদাহরণটি **Vue 3 + Axios** দিয়ে। React বা অন্য framework এ একই logic কাজ করবে।

### Step 1: Axios install করো

```bash
npm install axios
```

### Step 2: Axios instance বানাও

`src/api/axios.js` (বা `.ts`):

```js
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// প্রতিটি request এ automatically token বসাবে
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Token invalid/expired হলে logout করে দেবে
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
```

### Step 3: Register

```js
import api from '@/api/axios'

const register = async () => {
  try {
    const { data } = await api.post('/register', {
      name: 'Hassan',
      email: 'hassan@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    })
    console.log(data)
  } catch (error) {
    // Validation error (422)
    console.log(error.response?.data?.errors)
  }
}
```

### Step 4: Login করে token save করো

```js
const login = async () => {
  try {
    const { data } = await api.post('/login', {
      email: 'hassan@example.com',
      password: 'password123',
    })

    // Response এ token কোন key তে আসছে সেটা তোমার AuthController দেখে নিও
    // (যেমন data.token বা data.access_token)
    localStorage.setItem('token', data.token)
  } catch (error) {
    console.log(error.response?.data?.message)
  }
}
```

### Step 5: Public task list দেখাও (login ছাড়া)

```js
const tasks = ref([])

const fetchPublicTasks = async () => {
  const { data } = await api.get('/task-list')
  tasks.value = data.data ?? data // pagination/Resource থাকলে data.data
}
```

### Step 6: Protected Task CRUD

```js
// সব task
const { data } = await api.get('/tasks')

// নতুন task
await api.post('/tasks', { title: 'New task', description: 'Details' })

// একটি task
await api.get(`/tasks/${id}`)

// Update
await api.put(`/tasks/${id}`, { title: 'Updated title' })

// Delete
await api.delete(`/tasks/${id}`)
```

### Step 7: Logout

```js
const logout = async () => {
  await api.post('/logout')
  localStorage.removeItem('token')
  // login page এ redirect করো
}
```

### Step 8: Vue Router এ Route Protection (optional)

```js
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else {
    next()
  }
})
```

```js
{ path: '/tasks', component: TaskPage, meta: { requiresAuth: true } }
```

### Fetch API দিয়ে (Axios ছাড়া)

```js
const res = await fetch('http://127.0.0.1:8000/api/tasks', {
  headers: {
    Accept: 'application/json',
    Authorization: `Bearer ${localStorage.getItem('token')}`,
  },
})
const data = await res.json()
```

---

## CORS Setup

Frontend আলাদা port এ চললে (যেমন `http://localhost:5173`) CORS error আসতে পারে। Laravel এ `config/cors.php` না থাকলে বানিয়ে নাও:

```bash
php artisan config:publish cors
```

`config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:5173'],
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

পরিবর্তনের পর:

```bash
php artisan config:clear
```

> Token (Bearer) authentication ব্যবহার করলে `supports_credentials` `false` রাখাই যথেষ্ট।

---

## Testing

```bash
composer test
```

অথবা:

```bash
php artisan test
```

---

## Common Problems

| সমস্যা | সমাধান |
|---|---|
| `401 Unauthenticated` | `Authorization: Bearer TOKEN` header ঠিক আছে কিনা দেখো |
| Error এ HTML page/redirect আসছে | `Accept: application/json` header দাও |
| `422 Unprocessable Content` | Request body এর field ও validation rule মিলিয়ে দেখো |
| `SQLSTATE` / database error | `.env` এর DB config ঠিক আছে কিনা এবং `php artisan migrate` করা হয়েছে কিনা দেখো |
| CORS error | উপরের [CORS Setup](#cors-setup) অনুযায়ী `allowed_origins` এ frontend URL দাও |
| Config পরিবর্তন কাজ করছে না | `php artisan config:clear` ও `php artisan cache:clear` চালাও |

---

## Author

**Hassan** — [GitHub](https://github.com/hassanroki)

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
