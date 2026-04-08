# Deploy Safe Guide (Production)

Bu dokuman, canli ortamda veri kaybi olmadan guncelleme yapmak icin zorunlu adimlari icerir.

## 1) Asla kullanma (yasak)

- `php artisan migrate:fresh`
- `php artisan migrate:refresh`
- `php artisan migrate:reset`
- `php artisan db:wipe`
- `php artisan migrate:rollback` (onaysiz)

Bu komutlar tablolari silebilir veya geriye sarabilir.

## 2) Guvenli deploy akisi

```bash
# 1) Kod guncellemesi (Plesk Git Deploy/Pull)

# 2) Sadece non-destructive migration
php artisan migrate --force

# 3) Sadece idempotent patch seed
php artisan db:seed --class=Database\\Seeders\\Patches\\V1Seed --force

# 4) Cache temizle/yenile
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 3) Veri guvenligi kurali

- Her release oncesi veritabani yedegi al.
- Yeni degisiklikte:
  - tablo/kolon/index = migration
  - ayar/patch verisi = patch seeder (idempotent)
- Seeder icinde `truncate` veya toplu `delete` kullanma.

## 4) Hata durumunda

Migration hatasi alinirsa:

1. Deploy durdur.
2. Hangi migration kirildiysa sadece onu duzelt.
3. `migrate:fresh` yerine hedef migration `--path` ile calistir.
4. Gerekirse backup'tan geri don.
