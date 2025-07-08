# PETUNJUK INSTALASI DAN PENGGUNAAN
# CAT Laravel System

## LANGKAH 1: PERSIAPAN SISTEM

### Requirement Minimum:
- PHP 8.1 atau lebih tinggi
- MySQL/MariaDB
- Composer (untuk Laravel dependencies)
- Web browser modern

### Cek System Requirements:
```bash
php setup.php
```

## LANGKAH 2: INSTALASI COMPOSER DEPENDENCIES

### Install Laravel dan dependencies:
```bash
composer install
```

**Catatan**: Jika belum ada composer, download dari https://getcomposer.org

## LANGKAH 3: KONFIGURASI ENVIRONMENT

### Generate Application Key:
```bash
php artisan key:generate
```

### Edit file .env untuk database:
```
DB_DATABASE=cat_laravel
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

## LANGKAH 4: SETUP DATABASE

### Buat database MySQL:
```sql
CREATE DATABASE cat_laravel;
```

### Jalankan migrations:
```bash
php artisan migrate
```

### Import data item parameters:
```bash
php artisan db:seed
```

## LANGKAH 5: JALANKAN SERVER

### Opsi 1: Laravel Development Server
```bash
php artisan serve
```
Akses: http://localhost:8000

### Opsi 2: PHP Built-in Server
```bash
start_server.bat
```
Atau manual:
```bash
php -S localhost:8000 -t public
```

## LANGKAH 6: TESTING SISTEM

### Test Basic Functions:
```bash
php test_basic.php
```

### Test Web Interface:
1. Buka browser ke http://localhost:8000
2. Klik "Mulai Tes"
3. Jawab beberapa soal dengan "Benar" atau "Salah"
4. Lihat grafik perkembangan theta dan SE

## STRUKTUR PROJECT

```
LARAVEL/
├── app/
│   ├── Http/Controllers/
│   │   ├── CATController.php      # API endpoints
│   │   └── HomeController.php     # Web interface
│   ├── Models/
│   │   ├── ItemParameter.php      # Model item
│   │   ├── TestSession.php        # Model sesi tes
│   │   ├── TestResponse.php       # Model respon
│   │   └── UsedItem.php          # Model item terpakai
│   └── Services/
│       └── CATService.php         # Core CAT algorithms
├── database/
│   ├── migrations/                # Database schema
│   └── seeders/
│       └── ItemParametersSeeder.php # Import CSV
├── resources/views/cat/
│   └── index.blade.php           # Interface utama
├── routes/
│   └── web.php                   # URL routing
├── public/
│   └── index.php                 # Entry point
├── Parameter_Item_IST.csv        # Data 176 item
├── setup.php                     # Setup script
├── test_basic.php               # Basic test
├── start_server.bat             # Quick start
└── README.md                    # Dokumentasi
```

## API ENDPOINTS

### 1. Start Test
```
POST /api/start-test
Response: {
    "session_id": "CAT_1234567890_1234",
    "item": { item_data },
    "theta": 0.0,
    "se": 1.0,
    "item_number": 1
}
```

### 2. Submit Response
```
POST /api/submit-response
Body: {
    "session_id": "CAT_1234567890_1234",
    "item_id": "A01",
    "answer": 1
}
```

### 3. Session History
```
GET /api/session-history/{sessionId}
```

## FITUR SISTEM

### Algoritma CAT:
- **Model IRT 3PL**: P(θ) = g + (u-g)/(1 + exp(-a(θ-b)))
- **Estimasi EAP**: Expected A Posteriori dengan grid theta
- **Item Selection**: Expected Fisher Information (EFI)
- **Forcing Logic**: b_max untuk theta tinggi, b_min untuk theta rendah

### Kriteria Stopping:
1. SE ≤ 0.25 dengan minimal 10 soal
2. Maksimal 30 soal
3. Semua item terpakai
4. Item b_max/b_min sudah diberikan

### Change Control:
- Soal 1-5: Δθ ≤ 1.0
- Soal 6+: Δθ ≤ 0.25

## TROUBLESHOOTING

### Error: "Class not found"
**Solusi**: Jalankan `composer install`

### Error: "Database connection"
**Solusi**: 
1. Pastikan MySQL running
2. Cek kredensial di .env
3. Buat database: `CREATE DATABASE cat_laravel;`

### Error: "No items available"
**Solusi**: Jalankan `php artisan db:seed`

### Error: "Application key not set"
**Solusi**: Jalankan `php artisan key:generate`

### Error: "Storage directory not writable"
**Solusi**: Set permission folder storage

## PERBANDINGAN DENGAN PHP NATIVE

| Aspek | PHP Native | Laravel |
|-------|------------|---------|
| Setup | Manual copy files | Composer + Artisan |
| Database | PDO manual | Eloquent ORM |
| Routing | Manual PHP files | Laravel Router |
| Templates | HTML + JS | Blade Templates |
| Sessions | Custom implementation | Laravel Session |
| Validation | Manual checks | Laravel Validation |
| Logging | error_log() | Laravel Log facade |
| Security | Manual CSRF | Built-in CSRF protection |

## PENGEMBANGAN LANJUTAN

### Tambahan Fitur:
1. User authentication
2. Multiple test sessions per user
3. Result export (PDF/Excel)
4. Advanced analytics
5. Item response time tracking
6. Adaptive termination criteria

### Custom Configuration:
- Edit `config/app.php` untuk general settings
- Edit `config/database.php` untuk database config
- Edit `.env` untuk environment variables

## KONTAK & DUKUNGAN

Untuk pertanyaan atau masalah:
1. Cek dokumentasi di README.md
2. Jalankan test diagnostics
3. Periksa log di storage/logs/laravel.log
