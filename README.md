
---

# 🚀 Laravel 12 Fullstack Project (Laravel + JS + Python)

Proyek ini merupakan aplikasi fullstack yang dibangun menggunakan **Laravel 12** untuk backend, **JavaScript** untuk frontend, dan **Python** untuk layanan AI atau embedding. Proyek ini mengandalkan beberapa dependensi lintas bahasa, sehingga proses instalasi dan running harus mengikuti instruksi dengan seksama.

---

## 📁 Struktur Project

```

.
├── app/
├── resources/
├── public/
├── embedding\_service/         # Service Python untuk AI/embedding
│   ├── main.py
│   ├── requirements.txt
├── node\_modules/
├── .env                       # File environment Laravel
├── oauth-private.key          # Key private untuk OAuth
├── oauth-public.key           # Key public untuk OAuth
├── package.json
├── composer.json
└── ...

````

---

## ⚙️ Persiapan Awal

### 1. Clone Repository

```bash
git clone https://github.com/your-org/your-project.git
cd your-project
````

### 2. Copy File Konfigurasi

Pastikan file berikut tersedia di root project (bisa copy dari environment staging atau production):

```bash
cp oauth-private.key oauth-private.key
cp oauth-public.key oauth-public.key
cp .env.example .env
```

> 🔐 **Catatan:** File `.env` harus diisi sesuai konfigurasi lokal Anda (database, key, dsb).

---

## 📦 Instalasi Project

Jalankan perintah berikut untuk melakukan instalasi semua dependensi PHP, Node.js, dan Python:

```bash
npm run install-all-linux
```

Perintah ini secara otomatis akan menjalankan:

* `composer install` → menginstal dependensi Laravel
* `npm install` → menginstal dependensi frontend
* `cd embedding_service && pip install -r requirements.txt` → menginstal dependensi Python

---

## 🚀 Menjalankan Aplikasi

Setelah semua dependensi terpasang, jalankan aplikasi dengan perintah berikut:

```bash
npm run serve-all-linux
```

Perintah ini akan menjalankan secara paralel:

* `php artisan serve` → menjalankan server Laravel
* `cd embedding_service && uvicorn main:app --host 0.0.0.0 --port 8180 --reload` → menjalankan service Python

> 💡 Pastikan Anda sudah memiliki `concurrently` pada devDependencies agar `serve-all-linux` berjalan baik.

---

## 📝 Skrip Penting

Berikut adalah beberapa skrip penting dari `package.json`:

```json
"scripts": {
  "install-all-linux": "composer install && npm install && cd embedding_service && pip install -r requirements.txt",
  "serve-all-linux": "concurrently \"php artisan serve\" \"cd embedding_service && uvicorn main:app --host 0.0.0.0 --port 8180 --reload\""
}
```

---

## ✅ Prasyarat

Sebelum menjalankan proyek, pastikan Anda telah menginstal:

* PHP >= 8.2
* Composer
* Node.js & npm
* Python 3.10+ & pip
* `uvicorn`, `fastapi` (sudah termasuk di `requirements.txt`)
* `concurrently` (secara otomatis terinstal via `npm install`)

---

## 🤝 Kontribusi

Pull request selalu diterima. Pastikan untuk menjalankan semua test dan linting sebelum mengirim PR.

---

## 📄 Lisensi

Proyek ini berada di bawah lisensi MIT – silakan gunakan dan modifikasi sesuai kebutuhan.

---

```

Silakan sesuaikan bagian `git clone`, `requirements.txt`, dan struktur folder dengan detail aktual proyek Anda. Jika Anda ingin saya bantu menghasilkan versi markdown langsung yang bisa dipaste ke GitHub, saya juga bisa bantu.
```
