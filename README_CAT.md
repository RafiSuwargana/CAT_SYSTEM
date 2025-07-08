# CAT Laravel - Computer Adaptive Testing System

## Deskripsi
Sistem Computer Adaptive Testing (CAT) menggunakan Laravel Framework dengan implementasi model Item Response Theory (IRT) 3 Parameter Logistic (3PL) dan estimasi Expected A Posteriori (EAP). Sistem ini adalah versi Laravel dari project CAT PHP Native yang sudah ada.

## Fitur Utama

- **3PL IRT Model**: Menggunakan model Item Response Theory 3 Parameter Logistic
- **EAP Estimation**: Expected A Posteriori untuk estimasi kemampuan siswa  
- **Expected Fisher Information (EFI)**: Pemilihan item terbaik berdasarkan informasi Fisher yang diharapkan
- **Database Item IST**: 176 item parameter dari Intelligence Structure Test
- **Real-time Adaptation**: Penyesuaian tingkat kesulitan secara real-time
- **Session Management**: Pengelolaan sesi tes dengan riwayat lengkap
- **Responsive UI**: Interface yang sama dengan versi PHP Native menggunakan Bootstrap 5

## Struktur Database

### Tables
1. **item_parameters**: Parameter item IRT (a, b, g, u) dari CSV IST
2. **test_sessions**: Sesi tes siswa dengan theta dan SE
3. **test_responses**: Respon siswa per item dengan tracking theta
4. **used_items**: Tracking item yang sudah digunakan per sesi

## Instalasi

### 1. Requirements
- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Web server (Apache/Nginx) atau PHP built-in server

### 2. Setup Database
```sql
CREATE DATABASE cat_laravel;
```

### 3. Environment Setup
```bash
# Copy environment file
copy .env.example .env

# Update database credentials in .env
DB_DATABASE=cat_laravel
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Install Dependencies
```bash
composer install
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Seed Database dengan Item Parameters
```bash
php artisan db:seed
```

### 8. Start Server
```bash
php artisan serve
```

Akses: `http://localhost:8000`

## API Endpoints

### 1. Start Test
```
POST /api/start-test
Response: {
    "session_id": "CAT_1234567890_1234",
    "item": { item_object },
    "theta": 0.0,
    "se": 1.0,
    "item_number": 1,
    "probability": 0.5,
    "information": 1.2
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
Response: {
    "test_completed": false,
    "item": { next_item_object },
    "theta": 0.5,
    "se": 0.8,
    "item_number": 2,
    "probability": 0.6,
    "information": 1.5
}
```

### 3. Session History
```
GET /api/session-history/{sessionId}
Response: {
    "session": { session_object },
    "responses": [ response_objects ],
    "theta_history": [0, 0.2, 0.5, ...],
    "se_history": [1.0, 0.9, 0.8, ...]
}
```

## Algoritma CAT

### Item Selection
- **Maximum Information** (default): Pilih item dengan informasi Fisher maksimum
- **Forcing b_max**: Untuk theta tinggi, paksa item dengan b maksimum
- **Forcing b_min**: Untuk theta rendah, paksa item dengan b minimum

### Theta Estimation (EAP)
```
θ_EAP = Σ(θ_k × P(θ_k|responses))
SE_EAP = √(Var[θ|responses])
```

### Stopping Criteria
1. SE ≤ 0.25 dengan minimal 10 soal
2. Maksimal 30 soal
3. Semua item telah digunakan
4. Soal b_max/b_min sudah diberikan

### Change Control
- Soal 1-5: Δθ ≤ 1.0
- Soal 6+: Δθ ≤ 0.25

## Data Import

Sistem menggunakan file `Parameter_Item_IST.csv` yang berisi 176 item parameter dengan format:
- **ID**: Item identifier (A01, A02, ...)
- **a**: Discrimination parameter
- **b**: Difficulty parameter  
- **g**: Guessing parameter
- **u**: Upper asymptote (default 1.0)

## Struktur Code

```
app/
├── Http/Controllers/
│   ├── CATController.php      # API endpoints
│   └── HomeController.php     # Web interface
├── Models/
│   ├── ItemParameter.php      # Item model
│   ├── TestSession.php        # Session model
│   ├── TestResponse.php       # Response model
│   └── UsedItem.php          # Used items model
└── Services/
    └── CATService.php         # Core CAT algorithms

database/
├── migrations/                # Database structure
└── seeders/
    └── ItemParametersSeeder.php # CSV import

resources/views/cat/
└── index.blade.php           # Main interface
```

## Perbandingan dengan PHP Native

| Fitur | PHP Native | Laravel |
|-------|------------|---------|
| Framework | Custom | Laravel 10+ |
| Database | PDO | Eloquent ORM |
| Routing | Manual | Laravel Router |
| Views | HTML + JS | Blade Templates |
| API | Custom | Laravel Controller |
| Sessions | Custom | Laravel Session |
| Validation | Manual | Laravel Validation |
| Logging | error_log | Laravel Log |

## Testing

Untuk testing sistem:

1. **Manual Testing**: Akses web interface dan jalankan tes
2. **API Testing**: Gunakan Postman atau curl untuk test endpoint
3. **Database Testing**: Periksa data di database setelah tes

## Troubleshooting

### Database Connection Error
- Pastikan MySQL service berjalan
- Periksa credentials di `.env`
- Pastikan database `cat_laravel` sudah dibuat

### CSV Import Error
- Pastikan file `Parameter_Item_IST.csv` ada di root directory
- Periksa format CSV (header: ID,a,b,g,u)
- Jalankan `php artisan db:seed` untuk import ulang

### Item Selection Error
- Pastikan ada item di database
- Periksa log Laravel di `storage/logs/laravel.log`

## Development

Untuk development lebih lanjut:

1. **Model Validation**: Tambah validasi di model
2. **User Authentication**: Tambah sistem login/register
3. **Test History**: Simpan riwayat tes per user
4. **Advanced Analytics**: Tambah analisis statistik
5. **Export Results**: Export hasil ke PDF/Excel

## License

MIT License

## Contact

Untuk pertanyaan atau bug report, silakan buat issue di repository ini.
