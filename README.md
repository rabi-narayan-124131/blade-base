# **BladeBase Starter Kit 🚀**

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## 📌 About BladeBase

**BladeBase Starter Kit** is a minimalistic Laravel boilerplate providing a solid foundation with **Blade templates, Tailwind CSS, Vite, Pest for testing, and Laravel Debugbar** for debugging.

### 🚀 Why BladeBase?
- 🎉 **Quick Start:** No initial database setup needed; view `welcome.blade.php` instantly.
- 🎨 **Modern Styling:** Tailwind CSS integrated for responsive designs.
- ⚡ **Fast Build System:** Uses Vite for asset compilation.
- 🐞 **Optimized Debugging:** Laravel Debugbar pre-configured.
- 🧪 **Testing-Ready:** Ships with Pest PHP for unit and feature testing.

---

## ⚡️ Quick Start with Laravel Installer

Create a new project using the Laravel installer and BladeBase:

```bash
laravel new project-name --using=rabi-narayan-124131/blade-base
cd project-name
```

Then follow these steps:

---

1️⃣ **Run Application Without Database**
```bash
php artisan serve
```
_or_
```bash
composer run dev
```
> 🔗 **View BladeBase UI instantly at** `http://127.0.0.1:8000`

---

2️⃣ **Configure Database & Apply Migrations**
```bash
php artisan migrate
php artisan setup:db
```
> 💾 **Automatically switches session, queue, and cache to database storage.**

---

3️⃣ **Install Frontend Dependencies & Build Assets**
```bash
npm install
npm run build
```
> 🎨 **Tailwind CSS, Vite, and dependencies are installed.**

---

4️⃣ **Start Development Mode**
```bash
composer run dev
```
> ⚡ **Runs Laravel, queues, and Vite simultaneously.**

---

## 📦 Download as ZIP

Alternatively, download the project as a ZIP file:

---

1️⃣ Click the green **Code** button on GitHub, then select **Download ZIP**.  
2️⃣ Extract the ZIP file to your desired location.  
3️⃣ Open a terminal in the extracted folder.

> **⚠️ Important:**  
> If you downloaded the project as a ZIP file, **do not run the "Clone & Install Dependencies" step below**.  
> Start directly from the `composer install` command inside your extracted folder, then continue with the rest of the steps.

---

## 📖 Manual Installation Guide

---

1️⃣ **Clone & Install Dependencies**
```bash
git clone https://github.com/rabi-narayan-124131/blade-base.git
cd blade-base
composer install
```
> **Note:** If you downloaded as ZIP, skip `git clone` and `cd blade-base`. Just run `composer install` in your extracted folder.

---

2️⃣ **Setup Environment File**

Copy the `.env.example` file and rename it to `.env`:

On Linux/macOS:
```bash
cp .env.example .env
```
On Windows:
```bash
copy .env.example .env
```

Then generate the application key:
```bash
php artisan key:generate
```

---

3️⃣ **Run Application Without Database**
```bash
php artisan serve
```
_or_
```bash
composer run dev
```
> 🔗 **View BladeBase UI instantly at** `http://127.0.0.1:8000`

---

4️⃣ **Configure Database & Apply Migrations**
```bash
php artisan migrate
php artisan setup:db
```
> 💾 **Automatically switches session, queue, and cache to database storage.**

---

5️⃣ **Install Frontend Dependencies & Build Assets**
```bash
npm install
npm run build
```
> 🎨 **Tailwind CSS, Vite, and dependencies are installed.**

---

6️⃣ **Start Development Mode**
```bash
composer run dev
```
> ⚡ **Runs Laravel, queues, and Vite simultaneously.**

---

## 🛠 Core Dependencies

```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/tinker": "^2.10.1"
}
```

---

## 🎓 Learning Laravel

- 📚 [Laravel Docs](https://laravel.com/docs) — Extensive documentation covering all core features.

---

## 🔐 Security & Contributing

- 🛡 **Report vulnerabilities** to [Rabi Narayan](mailto:coolrabi9583@gmail.com).

---

## ⚡ Final Thoughts

✔ **No initial database required** — Explore instantly!  
✔ **Optimized Laravel development experience** with debugging tools included.  
✔ **Fast builds with Vite and Tailwind CSS.**  
✔ **Perfect for developers who need a clean, lightweight Laravel setup.**  

**Get started with BladeBase today! 🚀**

---

### 🛠 What’s Improved?
- Cleaner UI with sections clearly separated.
- More structured installation steps for better readability.
- Formatted features and dependencies for easy reference.
- Improved accessibility with icons and concise descriptions.

---