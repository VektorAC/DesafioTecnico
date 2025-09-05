# DesafioTecnico
Desafio tecnico para o backend con PHP, autenticación de usuarios e integración con APIs externas de plataformas de e-commerce como Shopify o WooCommerce.

Admin SPA + API para visualizar productos, pedidos y métricas de tiendas Shopify y WooCommerce, con exportación a CSV/XLSX. Pensado para correr en Docker (Nginx + PHP-FPM 8.3 + MySQL 8), frontend en React 18 + Vite + TypeScript + Ant Design + TanStack Query + Chart.js y backend en Laravel 12.

## Tabla de contenidos

- [Arquitectura](#arquitectura)  
- [Stack técnico](#stack-técnico)  
- [Requisitos](#requisitos)  
- [Configuración y arranque (Docker)](#configuración-y-arranque-docker)  
- [Variables de entorno](#variables-de-entorno)  
- [Base de datos](#base-de-datos)  
- [Autenticación (dev)](#autenticación-dev)  
- [Flujos de conexión de tiendas](#flujos-de-conexión-de-tiendas)  
- [Endpoints principales](#endpoints-principales)  
- [Frontend](#frontend)  
- [Métricas agregadas](#métricas-agregadas)  
- [Exportación CSV/XLSX](#exportación-csvxlsx)
- [Quickstart](#Quickstart)


## Arquitectura

**Backend (Laravel 12, PHP 8.3)**  
- Contrato `EcommerceProvider` y tres implementaciones:
  - `ShopifyProvider` (OAuth + REST Admin API).
  - `WooProvider` (REST + API Key).
  - `MockEcommerceProvider` (datos de prueba para desarrollo).
- `EcommerceProviderFactory` resuelve el proveedor según la tienda (`shop_id`).
- `ShopContext` localiza la tienda activa (por `shop_id` o última conectada).
- Credenciales/token cifrados en la tabla `shops`.

**Frontend (React + Vite + TS)**  
- SPA minimalista con Ant Design, TanStack Query y Chart.js.
- Páginas: **Login**, **Agregar tienda**, **Productos**, **Órdenes**, **Métricas**.

**Infra**  
- Docker Compose: Nginx, PHP-FPM 8.3, MySQL 8.

---

## Stack técnico

- **Backend:** Laravel 12, PHP 8.3, Composer 2  
- **DB:** MySQL 8 
- **Frontend:** React 18, Vite, TypeScript, Ant Design, TanStack Query, Axios, Chart.js  
- **Export:** `maatwebsite/excel` (para XLSX); CSV nativo  
- **Auth (dev):** Token Bearer (login hardcodeado)
- **Contenedores:** Nginx 1.27 + PHP-FPM 8.3 + MySQL 8

---

## Requisitos

- Docker y Docker Compose  
- Node.js 18+ y npm 9+ (para el frontend)

---
## Configuración y arranque (Docker)
1. **Clonar repo** e ingresar a `backend/`

2. **Docker Compose** (Nginx + PHP-FPM + MySQL):
   - `docker-compose.yml` (servicios `app`, `nginx`, `mysql`)
   - `Dockerfile` (PHP 8.3 con `pdo_mysql`, `zip`, `gd`, `intl`)
   - `nginx.conf` (root en `public/`, passthrough PHP a `app:9000`)

3. **Primera vez** (construir y levantar):
```bash
cd backend
docker compose up -d --build
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app composer require maatwebsite/excel

```
### Variables de entorno
Backend .env (ejemplo):
```bash
APP_NAME=Estudeo
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=America/Santiago
APP_URL=http://localhost

# DB
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=testTec
DB_USERNAME=test
DB_PASSWORD=secret

# Export / Moneda
CURRENCY=CLP

# Proveedores (OAuth/Keys)
SHOPIFY_API_KEY=
SHOPIFY_API_SECRET=
SHOPIFY_SCOPES=read_products,read_orders
SHOPIFY_API_VERSION=2025-07
SHOPIFY_REDIRECT_URI=${APP_URL}/shops/callback
```

Frontend .env (ejemplo):
```bash
VITE_API_URL=http://localhost
```

### Base de datos
- users 
- shops: credenciales cifradas por tienda
- sync_logs: logs de operaciones (export, listados, métricas)
  
**MySQL inicial**
  ```bash
  docker compose exec mysql mysql -utest -psecret -e "SHOW DATABASES;"
```
--- 
### Autenticación (dev)
login de desarrollo con token Bearer.
* Login
  ```bash
  { "username": "test", "password": "prueba123" }
  
  ```
--- 
### Flujos de conexión de tiendas
## Modo Mock (sin cuentas reales)
Si no hay tienda o faltan credenciales, la EcommerceProviderFactory cae en MockEcommerceProvider, para que la UI siempre tenga datos.
Shopify (OAuth)

## Shopify (OAuth)

1. Configura en el **Partner Dashboard / app**:
   - **App URL:** `http://localhost`
   - **Callback:** `http://localhost/shops/callback`
   - **Scopes:** `read_products, read_orders`
   - Coloca `SHOPIFY_API_KEY` y `SHOPIFY_API_SECRET` en el `.env` del backend.
2. Inicia OAuth desde el navegador:
GET /shops/connect?shop=tu-tienda.myshopify.com
3. Acepta permisos → redirige a `/shops/callback` → **guarda token cifrado** en `shops`.

---

## WooCommerce (API Key)

1. Genera **Consumer Key/Secret** desde Woo (permisos de **lectura**).
2. Front: formulario **“Agregar tienda Woo”** (`domain` + `ck` + `cs`).  
Back: `POST /shops/woo` con JSON:
```json
{ "domain": "https://miwp.com", "ck": "ck_xxx", "cs": "cs_xxx" }
```
---
### Endpoints principales
## Todos bajo Authorization: Bearer <token> (auth dev).

## Shops
* GET /api/shops – listar tiendas conectadas.
* GET /shops/connect – inicia OAuth de Shopify (web).
* GET /shops/callback – callback OAuth de Shopify (web).
* POST /shops/woo – registra tienda Woo por API Key.

## Productos

* GET /api/products?shop_id=&page=&per_page=&search=
* GET /api/export/products.csv?shop_id=&search=
* GET /api/export/products.xlsx?shop_id=&search=

## Pedidos

* GET /api/orders?shop_id=&from=&to=&status=&customer=
* GET /api/export/orders.csv?shop_id=&from=&to=
* GET /api/export/orders.xlsx?shop_id=&from=&to=

## Métricas

* GET /api/metrics?shop_id=&from=&to=
**Respuesta:** KPIs (orders, revenue, aov), monthly_sales (YYYY-MM),
top_products, status_breakdown, currency.

## cURL ejemplo
  ```bash
  TOKEN="ey..."
curl -H "Authorization: Bearer $TOKEN" "http://localhost/api/products?per_page=10"
curl -H "Authorization: Bearer $TOKEN" -OJ "http://localhost/api/export/orders.csv?from=2025-08-01&to=2025-09-05"
curl -H "Authorization: Bearer $TOKEN" "http://localhost/api/metrics?from=2025-08-01&to=2025-09-05"
  ```
--- 
## Frontend
### Crear proyecto y dependencias
```bash
cd frontend
npm i
npm run dev
```
### Páginas clave

* Login: token dev (usuario test, pass prueba123) → guarda en localStorage y setea Header Bearer para todas las requests.
* Agregar tienda: Shopify (input shop → OAuth) y Woo (form domain/ck/cs).
* Productos / pedidos: tablas con paginación y filtros básicos.
* Métricas: Chart.js (línea ventas mensuales / barras top productos).

## Métricas agregadas

* Endpoint: /api/metrics computa en runtime desde el proveedor (o Mock).
* KPIs: total de órdenes, revenue, AOV.
* Series: ventas por mes (YYYY-MM), top 10 productos por unidades, breakdown por estado.
* Frontend: react-chartjs-2 registrado en src/lib/charts.ts.

## Exportación CSV/XLSX

* CSV: nativo con streamDownload (cabeceras y filas normalizadas).
* XLSX: maatwebsite/excel (export genérico GenericArrayExport).
* Botones: en tablas de Productos/Órdenes, exportan con los mismos filtros activos.

--- 

## Quickstart 

## 1) Docker
```bash
cd backend
docker compose down -v
docker compose up -d --build
docker compose ps
```
## 2) Backend (Laravel)
```bash
docker compose exec app composer install
docker compose exec app cp .env.example .env
```
.env:
```bash
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=testTec
DB_USERNAME=test
DB_PASSWORD=secret
CURRENCY=CLP
```
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```
## 3) Frontend (Vite + React)
```bash
cd ../frontend
npm i
echo "VITE_API_URL=http://localhost" > .env
npm run dev
```
