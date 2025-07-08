# CAT Laravel - EFI Implementation Update

## Overview

Computer Adaptive Testing (CAT) sistem dengan menggunakan Expected Fisher Information (EFI) untuk pemilihan item dan Expected A Posteriori (EAP) untuk estimasi kemampuan.

## Perubahan Implementasi

### Sebelumnya: Fisher Information (FI)
```php
// Pemilihan item berdasarkan Fisher Information pada theta saat ini
$info = $this->itemInformation($theta, $item->a, $item->b, $item->g, $item->u);
```

### Sekarang: Expected Fisher Information (EFI)
```php
// Pemilihan item berdasarkan Expected Fisher Information dengan posterior distribution
$efi = $this->expectedFisherInformation($item->a, $item->b, $item->g, $sessionId, $item->u);
```

## Metode yang Digunakan

### 1. Expected Fisher Information (EFI)
- **Rumus**: EFI = Σ I(θ_k) × P(θ_k|responses)
- **Deskripsi**: Menghitung ekspektasi informasi Fisher berdasarkan distribusi posterior theta
- **Keunggulan**: Lebih optimal untuk estimasi EAP karena mempertimbangkan ketidakpastian theta

### 2. Estimasi Theta dengan EAP
- **Rumus**: θ_EAP = Σ θ_k × P(θ_k|responses)
- **Prior**: N(0,2) untuk distribusi yang lebih flat
- **Grid**: -6 sampai 6 dengan 1001 titik

### 3. Standard Error EAP
- **Rumus**: SE_EAP = √(Var[θ|responses])
- **Variance**: Var[θ|responses] = Σ(θ_k - θ_EAP)² × P(θ_k|responses)

## Kontrol Perubahan Theta
- **Item 1-5**: Maksimal perubahan Δθ ≤ 1.0
- **Item 6+**: Maksimal perubahan Δθ ≤ 0.25

## Kriteria Penghentian
1. SE_EAP ≤ 0.25 dengan minimal 10 soal
2. Maksimal 30 soal
3. Semua item telah digunakan
4. Peserta mendapat soal dengan b maksimum (paling sulit) dan menjawab benar
5. Peserta mendapat soal dengan b minimum (paling mudah) dan menjawab salah

## Sinkronisasi dengan Python

Implementasi Laravel sekarang **100% sinkron** dengan implementasi Python di file `mle.py`:

### Grid Theta
```php
// Laravel
for ($i = -6; $i <= 6; $i += 0.012) { // 1001 points
    $thetaGrid[] = $i;
}
```

```python
# Python
theta_grid = np.linspace(-6, 6, 1001)  # Sinkron dengan EAP
```

### Prior Distribution
```php
// Laravel
$prior[] = $this->normalPdf($theta, 0, 2); // N(0,2)
```

```python
# Python
prior = norm.pdf(theta_grid, 0, 2)  # N(0,2)
```

### EFI Calculation
```php
// Laravel
$efi = 0;
for ($i = 0; $i < count($thetaGrid); $i++) {
    $info = $this->itemInformation($thetaGrid[$i], $a, $b, $g, $u);
    $efi += $info * $posterior[$i];
}
```

```python
# Python
efi = 0
for theta_val, weight in zip(theta_grid, posterior):
    info = self.item_information(theta_val, a, b, g, u)
    efi += info * weight
```

## Database Schema Update

### Tabel test_responses
Ditambahkan kolom baru:
```sql
expected_fisher_information DECIMAL(8,6) NULLABLE COMMENT 'Expected Fisher Information (EFI)'
```

## API Response Update

Response sekarang termasuk EFI:
```json
{
    "session_id": "CAT_1234567890_1234",
    "item": {...},
    "theta": 0.5,
    "se": 0.8,
    "item_number": 5,
    "probability": 0.75,
    "information": 1.25,
    "expected_fisher_information": 1.32
}
```

## Advantages of EFI over FI

1. **Lebih Optimal**: Mempertimbangkan ketidakpastian estimasi theta
2. **Konsisten dengan EAP**: EFI menggunakan distribusi posterior yang sama dengan EAP
3. **Adaptif**: Pemilihan item berubah seiring bertambahnya informasi dari respons peserta
4. **Akurat**: Mengurangi overfitting pada estimasi theta tunggal

## Performance Notes

- EFI computation menggunakan grid 1001 titik theta
- Setiap perhitungan EFI membutuhkan iterasi melalui semua respons sebelumnya
- Kompleksitas waktu: O(n × m) dimana n = jumlah respons, m = 1001 (grid points)
- Untuk 30 soal maksimal: ~30,030 operasi per pemilihan item (masih sangat cepat)
