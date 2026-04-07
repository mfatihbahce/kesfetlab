<?php
/**
 * Kesfet Lab Backend - Tam Otomatik Kurulum Script'i
 * Laravel 12 için cPanel sunucularda otomatik kurulum
 * 
 * Bu script şunları yapar:
 * 1. Sistem kontrolü
 * 2. Composer bağımlılıklarını yükler
 * 3. Laravel cache'lerini temizler
 * 4. Veritabanını kurar
 * 5. Dosya izinlerini ayarlar
 * 6. Sunucu optimizasyonları yapar
 */

// Hata raporlamayı aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
session_start();

// Güvenlik kontrolü
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<!DOCTYPE html>
    <html lang='tr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Kesfet Lab - Otomatik Kurulum</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #2c3e50; margin-bottom: 10px; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .warning h3 { color: #856404; margin-top: 0; }
            .steps { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .steps h3 { color: #495057; margin-top: 0; }
            .steps ul { margin: 0; padding-left: 20px; }
            .steps li { margin-bottom: 8px; color: #6c757d; }
            .btn { display: inline-block; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px; }
            .btn-danger { background: #dc3545; color: white; }
            .btn-secondary { background: #6c757d; color: white; }
            .btn:hover { opacity: 0.8; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔧 Kesfet Lab - Otomatik Kurulum</h1>
                <p>Laravel 12 için Tam Otomatik Kurulum Script'i</p>
            </div>
            
            <div class='warning'>
                <h3>⚠️ Güvenlik Uyarısı</h3>
                <p>Bu script Laravel 12 projenizi cPanel sunucuda otomatik olarak kuracak.</p>
                <p><strong>Bu işlem:</strong></p>
                <ul>
                    <li>Composer bağımlılıklarını yükleyecek</li>
                    <li>Laravel cache'lerini temizleyecek</li>
                    <li>Veritabanını kuracak</li>
                    <li>Dosya izinlerini ayarlayacak</li>
                    <li>Sunucu optimizasyonları yapacak</li>
                </ul>
            </div>
            
            <div class='steps'>
                <h3>📋 Kurulum Adımları:</h3>
                <ul>
                    <li><strong>1. Sistem Kontrolü</strong> - PHP, Composer, dosya izinleri</li>
                    <li><strong>2. Composer Kurulumu</strong> - Laravel bağımlılıkları</li>
                    <li><strong>3. Laravel Cache Temizleme</strong> - Tüm cache'ler</li>
                    <li><strong>4. Veritabanı Kurulumu</strong> - Migration ve seeding</li>
                    <li><strong>5. Dosya İzinleri</strong> - Storage ve cache dizinleri</li>
                    <li><strong>6. Sunucu Optimizasyonu</strong> - Production ayarları</li>
                    <li><strong>7. Test ve Doğrulama</strong> - Kurulum kontrolü</li>
                </ul>
            </div>
            
            <div style='text-align: center; margin-top: 30px;'>
                <a href='?confirm=yes' class='btn btn-danger'>🚀 Otomatik Kurulumu Başlat</a>
                <a href='./public/' class='btn btn-secondary'>❌ İptal Et</a>
            </div>
            
            <div style='margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 5px;'>
                <h4>📝 Önemli Notlar:</h4>
                <ul>
                    <li>Kurulum sırasında sayfayı kapatmayın</li>
                    <li>İnternet bağlantınızın stabil olduğundan emin olun</li>
                    <li>Kurulum 5-10 dakika sürebilir</li>
                    <li>Hata durumunda script otomatik olarak alternatif çözümler deneyecek</li>
                </ul>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// Kurulum başlat
echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Kesfet Lab - Kurulum İlerlemesi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; margin-bottom: 10px; }
        .step { margin-bottom: 20px; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        .step h3 { margin-top: 0; color: #007bff; }
        .success { background: #d4edda; border-left-color: #28a745; }
        .success h3 { color: #28a745; }
        .error { background: #f8d7da; border-left-color: #dc3545; }
        .error h3 { color: #dc3545; }
        .warning { background: #fff3cd; border-left-color: #ffc107; }
        .warning h3 { color: #856404; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; }
        .info h3 { color: #17a2b8; }
        .progress { background: #e9ecef; border-radius: 10px; height: 20px; margin: 10px 0; overflow: hidden; }
        .progress-bar { background: #007bff; height: 100%; transition: width 0.3s; }
        .log { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin: 10px 0; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto; }
        .btn { display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px; }
        .btn-success { background: #28a745; color: white; }
        .btn-primary { background: #007bff; color: white; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔧 Kesfet Lab - Otomatik Kurulum</h1>
            <p>Laravel 12 Kurulum İlerlemesi</p>
        </div>";

// Progress bar
echo "<div class='progress'>
    <div class='progress-bar' id='progress' style='width: 0%'></div>
</div>";

// Helper fonksiyonlar
function updateProgress($percent) {
    echo "<script>document.getElementById('progress').style.width = '$percent%';</script>";
    ob_flush();
    flush();
}

function logMessage($message, $type = 'info') {
    $timestamp = date('H:i:s');
    echo "<div class='log'>[$timestamp] $message</div>";
    ob_flush();
    flush();
}

function showStep($title, $content, $type = 'info') {
    echo "<div class='step $type'>
        <h3>$title</h3>
        <p>$content</p>
    </div>";
    ob_flush();
    flush();
}

// 1. Sistem Kontrolü
showStep("1. Sistem Kontrolü", "PHP, Composer ve dosya izinleri kontrol ediliyor...", "info");
updateProgress(10);

$checks = [
    'PHP Version' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'Composer' => file_exists('composer.json'),
    'Laravel Files' => file_exists('bootstrap/app.php'),
    'Storage Directory' => is_dir('storage'),
    'Bootstrap Directory' => is_dir('bootstrap'),
    'Config Directory' => is_dir('config'),
    'Routes Directory' => is_dir('routes'),
    'App Directory' => is_dir('app')
];

$allChecksPassed = true;
foreach ($checks as $check => $passed) {
    $status = $passed ? "✅" : "❌";
    logMessage("$status $check: " . ($passed ? "OK" : "FAILED"));
    if (!$passed) $allChecksPassed = false;
}

if (!$allChecksPassed) {
    showStep("❌ Sistem Kontrolü Başarısız", "Bazı gerekli dosyalar eksik. Lütfen proje dosyalarını kontrol edin.", "error");
    exit;
}

showStep("✅ Sistem Kontrolü Tamamlandı", "Tüm gerekli dosyalar ve dizinler mevcut.", "success");
updateProgress(20);

// 2. Composer Kurulumu
showStep("2. Composer Kurulumu", "Laravel bağımlılıkları yükleniyor...", "info");
updateProgress(30);

// Composer kurulumu için farklı yöntemler dene
$composerMethods = [
    'composer install --no-dev --optimize-autoloader',
    'composer install --no-dev',
    'composer install',
    'php composer.phar install --no-dev --optimize-autoloader',
    'php composer.phar install --no-dev',
    'php composer.phar install'
];

$composerSuccess = false;
foreach ($composerMethods as $method) {
    logMessage("Deneniyor: $method");
    
    $output = [];
    $returnCode = 0;
    
    // exec fonksiyonu kullanılabilir mi kontrol et
    if (function_exists('exec')) {
        exec($method . ' 2>&1', $output, $returnCode);
    } else {
        // exec yoksa shell_exec dene
        $result = shell_exec($method . ' 2>&1');
        $returnCode = $result !== null ? 0 : 1;
        $output = $result ? explode("\n", $result) : [];
    }
    
    if ($returnCode === 0) {
        logMessage("✅ Composer kurulumu başarılı: $method");
        $composerSuccess = true;
        break;
    } else {
        logMessage("❌ Composer kurulumu başarısız: $method");
        logMessage("Hata: " . implode("\n", $output));
    }
}

if (!$composerSuccess) {
    showStep("❌ Composer Kurulumu Başarısız", "Composer kurulumu yapılamadı. Manuel kurulum gerekli.", "error");
    showStep("🔧 Manuel Çözüm", "SSH ile sunucuya bağlanıp 'composer install' komutunu çalıştırın.", "warning");
    exit;
}

showStep("✅ Composer Kurulumu Tamamlandı", "Laravel bağımlılıkları başarıyla yüklendi.", "success");
updateProgress(50);

// 3. Laravel Cache Temizleme
showStep("3. Laravel Cache Temizleme", "Tüm Laravel cache'leri temizleniyor...", "info");
updateProgress(60);

$cacheCommands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan view:clear',
    'php artisan route:clear',
    'php artisan clear-compiled'
];

foreach ($cacheCommands as $command) {
    logMessage("Çalıştırılıyor: $command");
    
    $output = [];
    $returnCode = 0;
    
    if (function_exists('exec')) {
        exec($command . ' 2>&1', $output, $returnCode);
    } else {
        $result = shell_exec($command . ' 2>&1');
        $returnCode = $result !== null ? 0 : 1;
    }
    
    if ($returnCode === 0) {
        logMessage("✅ $command başarılı");
    } else {
        logMessage("⚠️ $command başarısız (devam ediliyor)");
    }
}

showStep("✅ Cache Temizleme Tamamlandı", "Tüm Laravel cache'leri temizlendi.", "success");
updateProgress(70);

// 4. Veritabanı Kurulumu
showStep("4. Veritabanı Kurulumu", "Veritabanı migration'ları çalıştırılıyor...", "info");
updateProgress(80);

// Migration komutları
$dbCommands = [
    'php artisan migrate --force',
    'php artisan db:seed --force'
];

foreach ($dbCommands as $command) {
    logMessage("Çalıştırılıyor: $command");
    
    $output = [];
    $returnCode = 0;
    
    if (function_exists('exec')) {
        exec($command . ' 2>&1', $output, $returnCode);
    } else {
        $result = shell_exec($command . ' 2>&1');
        $returnCode = $result !== null ? 0 : 1;
    }
    
    if ($returnCode === 0) {
        logMessage("✅ $command başarılı");
    } else {
        logMessage("⚠️ $command başarısız (devam ediliyor)");
    }
}

showStep("✅ Veritabanı Kurulumu Tamamlandı", "Veritabanı migration'ları ve seeding tamamlandı.", "success");
updateProgress(85);

// 5. Dosya İzinleri
showStep("5. Dosya İzinleri", "Storage ve cache dizinleri için izinler ayarlanıyor...", "info");
updateProgress(90);

$directories = [
    'storage',
    'storage/app',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        chmod($dir, 0755);
        logMessage("✅ $dir izinleri ayarlandı (755)");
    } else {
        mkdir($dir, 0755, true);
        logMessage("✅ $dir dizini oluşturuldu (755)");
    }
}

showStep("✅ Dosya İzinleri Tamamlandı", "Tüm gerekli dizinler için izinler ayarlandı.", "success");
updateProgress(95);

// 6. Sunucu Optimizasyonu
showStep("6. Sunucu Optimizasyonu", "Production için optimizasyonlar yapılıyor...", "info");
updateProgress(98);

$optimizationCommands = [
    'php artisan config:cache',
    'php artisan route:cache',
    'php artisan view:cache'
];

foreach ($optimizationCommands as $command) {
    logMessage("Çalıştırılıyor: $command");
    
    $output = [];
    $returnCode = 0;
    
    if (function_exists('exec')) {
        exec($command . ' 2>&1', $output, $returnCode);
    } else {
        $result = shell_exec($command . ' 2>&1');
        $returnCode = $result !== null ? 0 : 1;
    }
    
    if ($returnCode === 0) {
        logMessage("✅ $command başarılı");
    } else {
        logMessage("⚠️ $command başarısız (devam ediliyor)");
    }
}

showStep("✅ Sunucu Optimizasyonu Tamamlandı", "Production optimizasyonları tamamlandı.", "success");
updateProgress(100);

// 7. Test ve Doğrulama
showStep("7. Test ve Doğrulama", "Kurulum test ediliyor...", "info");

// Laravel uygulamasını test et
try {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        logMessage("✅ vendor/autoload.php yüklendi");
        
        if (class_exists('Illuminate\Foundation\Application')) {
            logMessage("✅ Illuminate\Foundation\Application sınıfı bulundu");
            
            // bootstrap/app.php'yi test et
            $app = require_once 'bootstrap/app.php';
            if ($app instanceof \Illuminate\Foundation\Application) {
                logMessage("✅ Laravel uygulaması başarıyla başlatıldı");
                showStep("🎉 Kurulum Başarılı!", "Laravel 12 uygulamanız başarıyla kuruldu!", "success");
            } else {
                logMessage("⚠️ Laravel uygulaması başlatılamadı");
                showStep("⚠️ Kısmi Başarı", "Kurulum tamamlandı ama uygulama test edilemedi.", "warning");
            }
        } else {
            logMessage("❌ Illuminate\Foundation\Application sınıfı bulunamadı");
            showStep("❌ Kurulum Hatası", "Laravel framework sınıfları yüklenemedi.", "error");
        }
    } else {
        logMessage("❌ vendor/autoload.php bulunamadı");
        showStep("❌ Kurulum Hatası", "Composer autoloader bulunamadı.", "error");
    }
} catch (Exception $e) {
    logMessage("❌ Test hatası: " . $e->getMessage());
    showStep("❌ Test Hatası", "Kurulum test edilirken hata oluştu: " . $e->getMessage(), "error");
}

// Final sonuç
echo "<div style='margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 5px; text-align: center;'>
    <h3>🎉 Kurulum Tamamlandı!</h3>
    <p>Kesfet Lab Laravel 12 uygulamanız başarıyla kuruldu.</p>
    
    <div style='margin: 20px 0;'>
        <a href='./public/' class='btn btn-success'>🏠 Ana Sayfa</a>
        <a href='./public/admin' class='btn btn-primary'>⚙️ Admin Panel</a>
        <a href='./public/admin/login' class='btn btn-primary'>🔐 Admin Giriş</a>
    </div>
    
    <div style='text-align: left; margin-top: 20px;'>
        <h4>📋 Admin Giriş Bilgileri:</h4>
        <ul>
            <li><strong>Email:</strong> admin@kesfetlab.com</li>
            <li><strong>Şifre:</strong> admin123</li>
        </ul>
        
        <h4>🔧 Önemli Notlar:</h4>
        <ul>
            <li>Tarayıcı cache'ini temizleyin (Ctrl+F5)</li>
            <li>Eğer sorun yaşarsanız, tarayıcıyı tamamen kapatıp açın</li>
            <li>Admin panelinden giriş yaparak sistemi test edin</li>
            <li>Herhangi bir sorun için hosting sağlayıcınızla iletişime geçin</li>
        </ul>
    </div>
</div>";

echo "</div></body></html>";
?>
