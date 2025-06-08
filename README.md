## 📦 Store View Plugin for Craft CMS

Track total, daily, weekly, and monthly views for entries, categories, tags, and custom routes. Fully supports multi-site setups, Blitz static caching, and works with Craft CMS 4 and 5.

---

## ✨ Main Features

* Track views of Entries, Categories, Tags, and custom routes
* Multi-site support
* View counting via AJAX/fetch for Blitz/static cache compatibility
* Compatible with Craft CMS 4 and 5

---

## 🛠 Installation

Install via the Craft Plugin Store or with Composer:

```bash
composer require nelson-nguyen/craft-store-view
```

Search for "Store View" in the [Craft Plugin Store](https://plugins.craftcms.com/search?q=view+store&tab=plugins) and install from the Control Panel.

---

## 🧹 Reset Views Command
To clear all stored view counts (total, daily, weekly, monthly), run the following console command manually:
```bash
./craft store-view/reset-view
```

---

## ⏰ Automate with Cron Job
You can schedule this command to run automatically every day at midnight using cron.

1. Open your server's crontab editor:
```bash
crontab -e
```

2. Add this line to run the reset command daily at midnight:
```bash
0 0 * * * /path/to/craft store-view/reset-view >/dev/null 2>&1
```
Make sure to replace /path/to/craft with the full path to your Craft CMS craft executable.


3. Save and exit the editor.

This will reset your view counts daily at 00:00 server time.

---

## 🔢 Count Views

Manually trigger a view count for any supported element or custom URI:

```twig
{# Entry #}
{% do craft.storeView.count(entry.id) %}

{# Category #}
{% do craft.storeView.count(category.id) %}

{# Tag #}
{% do craft.storeView.count(tag.id) %}

{# Custom route (e.g., static page) #}
{% do craft.storeView.count(craft.app.request.getPathInfo()) %}
```

### 🔁 AJAX View Tracking (For Blitz/Static Cache Support)

Use the following code to send an AJAX request that registers a view:

```twig
{% js %}
fetch('/store-view/api/track-page-view' + window.location.search, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-Token': '{{ craft.app.request.csrfToken }}'
  },
  body: JSON.stringify({
    elementId: {{ entry.id ?? 'null' }},
    siteId: {{ currentSite.id ?? 'null' }},
    uri: window.location.pathname
  }),
});
{% endjs %}
```

---

## 🔍 Querying Store Views

Use `craft.storeView.entries()` to retrieve and filter view statistics with a fluent API.

### 🔧 Basic Usage

```twig
{% set views = craft.storeView.entries().all() %}
```

### 🔍 Filter by Section

```twig
{% set blogViews = craft.storeView.entries().sections('blog').all() %}
```

Multiple sections:

```twig
{% set views = craft.storeView.entries().sections(['blog', 'news']).all() %}
```

### 🔍 Filter by Category Group

```twig
{% set views = craft.storeView.entries().categories('topics').all() %}
```

### 🔍 Filter by Tag Group

```twig
{% set views = craft.storeView.entries().tags('labels').all() %}
```

### 🕒 Filter by Date Range

```twig
{% set todayViews = craft.storeView.entries().withRange('today').all() %}
{% set thisWeekViews = craft.storeView.entries().withRange('thisWeek').all() %}
{% set thisMonthViews = craft.storeView.entries().withRange('thisMonth').all() %}
```

### 🛠 Custom Filters

```twig
{% set views = craft.storeView.entries()
    .where({ elementId: 123 })
    .limit(10)
    .offset(5)
    .orderBy('total DESC')
    .all() %}
```

### 📄 Get One Record

```twig
{% set view = craft.storeView.entries().where({ elementId: 123 }).one() %}
```

### 🔢 Count Total

```twig
{% set count = craft.storeView.entries().sections('blog').count() %}
```

---

## 🧱 Data Structure

Each result is an instance of `nelsonnguyen\craftstoreview\models\StoreViewModel`.

### Example Output:

```php
nelsonnguyen\craftstoreview\models\StoreViewModel {
  id: 1,
  uri: "custom/custom",
  elementId: null,
  siteId: 1,
  total: 2,
  day: 2,
  week: 2,
  month: 2,
  lastUpdated: DateTimeImmutable('2025-06-08 07:10:33.0 UTC'),
  element: null,
}
```

Or with populated element:

```php
nelsonnguyen\craftstoreview\models\StoreViewModel {
  id: 1,
  uri: "custom/custom",
  elementId: null,
  siteId: 1,
  total: 2,
  day: 2,
  week: 2,
  month: 2,
  lastUpdated: DateTimeImmutable('2025-06-08 07:10:33.0 UTC'),
  element: {
    id: 2,
    title: "test channel",
    slug: "test-channel",
    uri: "channel/test-channel",
    type: "Entry"
  }
}
```

---

## 📘 Notes

* `.sections()`, `.categories()`, and `.tags()` accept a string or array of handles.
* `.withRange()` accepts: `'today'`, `'thisWeek'`, `'thisMonth'`
* All methods are chainable.

---

## 📄 License

This is a commercial plugin available via the [Craft CMS Plugin Store](https://plugins.craftcms.com).
