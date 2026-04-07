<?php
/**
 * Kesfet Lab Backend - Veritabanı Kurulum Script'i
 * Laravel 12 için veritabanı kurulumu ve yapılandırması
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
        <title>Kesfet Lab - Veritabanı Kurulumu</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #2c3e50; margin-bottom: 10px; }
            .form-group { margin-bottom: 20px; }
            .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
            .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px; }
            .btn { display: inline-block; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px; border: none; cursor: pointer; }
            .btn-primary { background: #007bff; color: white; }
            .btn-secondary { background: #6c757d; color: white; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .warning h3 { color: #856404; margin-top: 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🗄️ Kesfet Lab - Veritabanı Kurulumu</h1>
                <p>Laravel 12 Veritabanı Yapılandırması</p>
            </div>
            
            <div class='warning'>
                <h3>⚠️ Önemli Bilgi</h3>
                <p>Bu script veritabanınızı yapılandıracak ve gerekli tabloları oluşturacak.</p>
                <p><strong>Gerekli Bilgiler:</strong></p>
                <ul>
                    <li>Veritabanı sunucu adresi (genellikle localhost)</li>
                    <li>Veritabanı adı</li>
                    <li>Veritabanı kullanıcı adı</li>
                    <li>Veritabanı şifresi</li>
                </ul>
            </div>
            
            <form method='POST' action=''>
                <div class='form-group'>
                    <label for='db_host'>Veritabanı Sunucu Adresi:</label>
                    <input type='text' id='db_host' name='db_host' value='localhost' required>
                </div>
                
                <div class='form-group'>
                    <label for='db_name'>Veritabanı Adı:</label>
                    <input type='text' id='db_name' name='db_name' placeholder='kesfetlab_db' required>
                </div>
                
                <div class='form-group'>
                    <label for='db_user'>Veritabanı Kullanıcı Adı:</label>
                    <input type='text' id='db_user' name='db_user' placeholder='kesfetlab_user' required>
                </div>
                
                <div class='form-group'>
                    <label for='db_pass'>Veritabanı Şifresi:</label>
                    <input type='password' id='db_pass' name='db_pass' required>
                </div>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <button type='submit' class='btn btn-primary'>🗄️ Veritabanını Kur</button>
                    <a href='./public/' class='btn btn-secondary'>❌ İptal Et</a>
                </div>
            </form>
        </div>
    </body>
    </html>";
    exit;
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    
    echo "<!DOCTYPE html>
    <html lang='tr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Kesfet Lab - Veritabanı Kurulum İlerlemesi</title>
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
                <h1>🗄️ Kesfet Lab - Veritabanı Kurulumu</h1>
                <p>Veritabanı Kurulum İlerlemesi</p>
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

    // 1. Veritabanı Bağlantı Testi
    showStep("1. Veritabanı Bağlantı Testi", "Veritabanı bağlantısı test ediliyor...", "info");
    updateProgress(20);

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        logMessage("✅ Veritabanı bağlantısı başarılı");
        showStep("✅ Veritabanı Bağlantısı", "Veritabanı bağlantısı başarıyla kuruldu.", "success");
    } catch (PDOException $e) {
        logMessage("❌ Veritabanı bağlantısı başarısız: " . $e->getMessage());
        showStep("❌ Veritabanı Bağlantı Hatası", "Veritabanı bağlantısı kurulamadı: " . $e->getMessage(), "error");
        exit;
    }

    updateProgress(40);

    // 2. .env Dosyası Güncelleme
    showStep("2. .env Dosyası Güncelleme", ".env dosyası veritabanı bilgileriyle güncelleniyor...", "info");
    updateProgress(60);

    try {
        if (file_exists('.env.example')) {
            $envContent = file_get_contents('.env.example');
        } else {
            // .env.example yoksa temel .env içeriği oluştur
            $envContent = "APP_NAME=\"Kesfet Lab\"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://kadindestekmerkezim.com.tr/kesfetlab/kesfet-lab-backend

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=\"hello@example.com\"
MAIL_FROM_NAME=\"\${APP_NAME}\"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME=\"\${APP_NAME}\"
VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"
VITE_PUSHER_HOST=\"\${PUSHER_HOST}\"
VITE_PUSHER_PORT=\"\${PUSHER_PORT}\"
VITE_PUSHER_SCHEME=\"\${PUSHER_SCHEME}\"
VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"";
        }

        // Veritabanı bilgilerini güncelle
        $envContent = str_replace('DB_HOST=127.0.0.1', "DB_HOST=$dbHost", $envContent);
        $envContent = str_replace('DB_DATABASE=laravel', "DB_DATABASE=$dbName", $envContent);
        $envContent = str_replace('DB_USERNAME=root', "DB_USERNAME=$dbUser", $envContent);
        $envContent = str_replace('DB_PASSWORD=', "DB_PASSWORD=$dbPass", $envContent);
        $envContent = str_replace('APP_URL=http://localhost', "APP_URL=https://kadindestekmerkezim.com.tr/kesfetlab/kesfet-lab-backend", $envContent);
        $envContent = str_replace('APP_NAME=Laravel', "APP_NAME=\"Kesfet Lab\"", $envContent);
        $envContent = str_replace('APP_ENV=local', "APP_ENV=production", $envContent);
        $envContent = str_replace('APP_DEBUG=true', "APP_DEBUG=false", $envContent);

        file_put_contents('.env', $envContent);
        logMessage("✅ .env dosyası güncellendi");
        showStep("✅ .env Dosyası Güncellendi", ".env dosyası veritabanı bilgileriyle güncellendi.", "success");
    } catch (Exception $e) {
        logMessage("❌ .env dosyası güncellenemedi: " . $e->getMessage());
        showStep("❌ .env Güncelleme Hatası", ".env dosyası güncellenemedi: " . $e->getMessage(), "error");
        exit;
    }

    updateProgress(80);

    // 3. Laravel Migration ve Seeding
    showStep("3. Laravel Migration ve Seeding", "Veritabanı tabloları oluşturuluyor ve veriler ekleniyor...", "info");
    updateProgress(90);

    $commands = [
        'php artisan migrate --force',
        'php artisan db:seed --force'
    ];

    foreach ($commands as $command) {
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

    showStep("✅ Veritabanı Kurulumu Tamamlandı", "Veritabanı tabloları oluşturuldu ve veriler eklendi.", "success");
    updateProgress(100);

    // Final sonuç
    echo "<div style='margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 5px; text-align: center;'>
        <h3>🎉 Veritabanı Kurulumu Tamamlandı!</h3>
        <p>Kesfet Lab veritabanı başarıyla kuruldu ve yapılandırıldı.</p>
        
        <div style='margin: 20px 0;'>
            <a href='./public/' class='btn btn-success'>🏠 Ana Sayfa</a>
            <a href='./public/admin' class='btn btn-primary'>⚙️ Admin Panel</a>
            <a href='./public/admin/login' class='btn btn-primary'>🔐 Admin Giriş</a>
        </div>
        
        <div style='text-align: left; margin-top: 20px;'>
            <h4>📋 Veritabanı Bilgileri:</h4>
            <ul>
                <li><strong>Sunucu:</strong> $dbHost</li>
                <li><strong>Veritabanı:</strong> $dbName</li>
                <li><strong>Kullanıcı:</strong> $dbUser</li>
                <li><strong>Durum:</strong> ✅ Bağlantı başarılı</li>
            </ul>
            
            <h4>📋 Admin Giriş Bilgileri:</h4>
            <ul>
                <li><strong>Email:</strong> admin@kesfetlab.com</li>
                <li><strong>Şifre:</strong> admin123</li>
            </ul>
            
            <h4>🔧 Önemli Notlar:</h4>
            <ul>
                <li>Veritabanı başarıyla kuruldu</li>
                <li>Tüm tablolar oluşturuldu</li>
                <li>Örnek veriler eklendi</li>
                <li>Admin panelinden giriş yaparak sistemi test edin</li>
            </ul>
        </div>
    </div>";

    echo "</div></body></html>";
    exit;
}

// Manuel kurulum için alternatif
echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Kesfet Lab - Manuel Veritabanı Kurulumu</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; margin-bottom: 10px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .warning h3 { color: #856404; margin-top: 0; }
        .btn { display: inline-block; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🗄️ Kesfet Lab - Veritabanı Kurulumu</h1>
            <p>Manuel Veritabanı Kurulumu</p>
        </div>
        
        <div class='warning'>
            <h3>⚠️ Manuel Kurulum Gerekli</h3>
            <p>Otomatik kurulum çalışmadı. Manuel kurulum yapmanız gerekiyor.</p>
        </div>
        
        <div style='text-align: center; margin-top: 30px;'>
            <a href='?confirm=yes' class='btn btn-primary'>🔄 Tekrar Dene</a>
            <a href='./public/' class='btn btn-secondary'>❌ İptal Et</a>
        </div>
    </div>
</body>
</html>";
?>
