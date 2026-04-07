<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple test response for direct access (only when no specific endpoint is requested)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_POST) && isset($path) && $path === 'api.php') {
    echo json_encode([
        'status' => 'success',
        'message' => 'API endpoint is working',
        'available_endpoints' => [
            'POST /api/instructor/login',
            'POST /api/instructor/logout',
            'GET /api/instructor/profile',
            'GET /api/instructor/groups',
            'GET /api/instructor/today-classes',
            'GET /api/instructor/groups/{id}',
            'PUT /api/instructor/change-password'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Load Laravel
require_once __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the request
$request = Illuminate\Http\Request::capture();

// Get the actual path from REQUEST_URI
$requestUri = $_SERVER['REQUEST_URI'];
$path = str_replace('/kesfet-guncel/kesfet-lab-backend/public/', '', $requestUri);
$path = ltrim($path, '/');

// Remove query string if present
if (strpos($path, '?') !== false) {
    $path = substr($path, 0, strpos($path, '?'));
}

$method = $request->method();

// Handle paths that start with 'api.php/'
if (strpos($path, 'api.php/') === 0) {
    $path = str_replace('api.php/', 'api/', $path);
}

// If the path is just 'api.php', we need to get the actual endpoint from the request
if ($path === 'api.php') {
    // Check if there's a specific endpoint in the request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // For login requests, we'll handle them directly
    if ($method === 'POST' && isset($data['phone']) && isset($data['password'])) {
        $controller = new App\Http\Controllers\Api\InstructorController();
        $response = $controller->login($request);
        $response->send();
        exit;
    }
    
    // For other requests, we need to determine the endpoint
    // This is a simplified approach - we'll handle common cases
    if ($method === 'GET') {
        // Check if there are query parameters that indicate the endpoint
        if (isset($_GET['endpoint'])) {
            $path = $_GET['endpoint'];
        }
    }
}

// Debug: Log the path for troubleshooting
error_log("API Request - Path: " . $path . ", Method: " . $method);

// API Routes
if ($path === 'api/instructor/login' && $method === 'POST') {
    $controller = new App\Http\Controllers\Api\InstructorController();
    $response = $controller->login($request);
    $response->send();
    exit;
}

// Veli routes
if ($path === 'api/parent/register' && $method === 'POST') {
    $controller = new App\Http\Controllers\Api\ParentController();
    $response = $controller->register($request);
    $response->send();
    exit;
}

if ($path === 'api/parent/login' && $method === 'POST') {
    $controller = new App\Http\Controllers\Api\ParentController();
    $response = $controller->login($request);
    $response->send();
    exit;
}

// Protected routes - require authentication
$protectedRoutes = [
    'api/instructor/logout',
    'api/instructor/profile',
    'api/instructor/groups',
    'api/instructor/today-classes',
    'api/instructor/change-password',
    'api/instructor/groups/{groupId}/students',
    'api/instructor/groups/{groupId}/attendance',
    'api/instructor/groups/{groupId}/attendance-status',
    'api/parent/logout',
    'api/parent/profile',
    'api/parent/change-password',
    'api/parent/add-student',
    'api/parent/students',
    'api/parent/students/{studentId}',
    'api/parent/students/{studentId}/attendance',
    'api/parent/notifications'
];

$isProtectedRoute = false;
foreach ($protectedRoutes as $route) {
    if ($path === $route || strpos($path, 'api/instructor/groups/') === 0 || strpos($path, 'api/parent/') === 0) {
        $isProtectedRoute = true;
        break;
    }
}

if ($isProtectedRoute) {
    // Apply authentication middleware
    $middleware = new App\Http\Middleware\SimpleTokenAuth();
    $response = $middleware->handle($request, function($request) use ($path, $method) {
        // Determine which controller to use based on the path
        if (strpos($path, 'api/parent/') === 0) {
            $controller = new App\Http\Controllers\Api\ParentController();
        } else {
            $controller = new App\Http\Controllers\Api\InstructorController();
        }
        
        if ($path === 'api/instructor/logout' && $method === 'POST') {
            return $controller->logout($request);
        }
        
        if ($path === 'api/instructor/profile' && $method === 'GET') {
            return $controller->profile($request);
        }
        
        if ($path === 'api/instructor/groups' && $method === 'GET') {
            return $controller->groups($request);
        }
        
        if ($path === 'api/instructor/today-classes' && $method === 'GET') {
            return $controller->todayClasses($request);
        }
        
        if (preg_match('/^api\/instructor\/groups\/(\d+)$/', $path, $matches) && $method === 'GET') {
            return $controller->groupDetail($request, $matches[1]);
        }
        
        if (preg_match('/^api\/instructor\/groups\/(\d+)\/students$/', $path, $matches) && $method === 'GET') {
            return $controller->getGroupStudents($request, $matches[1]);
        }
        
        if (preg_match('/^api\/instructor\/groups\/(\d+)\/attendance-status$/', $path, $matches) && $method === 'GET') {
            return $controller->checkAttendanceStatus($request, $matches[1]);
        }
        
        if (preg_match('/^api\/instructor\/groups\/(\d+)\/attendance$/', $path, $matches) && $method === 'POST') {
            return $controller->saveAttendance($request, $matches[1]);
        }
        
        if ($path === 'api/instructor/change-password' && $method === 'PUT') {
            return $controller->changePassword($request);
        }
        
        // Veli routes
        if ($path === 'api/parent/logout' && $method === 'POST') {
            return $controller->logout($request);
        }
        
        if ($path === 'api/parent/profile' && $method === 'GET') {
            return $controller->profile($request);
        }
        
        if ($path === 'api/parent/change-password' && $method === 'PUT') {
            return $controller->changePassword($request);
        }
        
        if ($path === 'api/parent/add-student' && $method === 'POST') {
            return $controller->addStudent($request);
        }
        
        if ($path === 'api/parent/students' && $method === 'GET') {
            return $controller->students($request);
        }
        
        if (preg_match('/^api\/parent\/students\/(\d+)$/', $path, $matches) && $method === 'GET') {
            return $controller->studentDetail($request, $matches[1]);
        }
        
        if (preg_match('/^api\/parent\/students\/(\d+)\/attendance$/', $path, $matches) && $method === 'GET') {
            return $controller->studentAttendance($request, $matches[1]);
        }
        
        if ($path === 'api/parent/notifications' && $method === 'GET') {
            return $controller->notifications($request);
        }
        
        return response()->json(['error' => 'Route not found'], 404);
    });
    
    $response->send();
    exit;
}

// 404 for unknown routes
http_response_code(404);
echo json_encode(['error' => 'Route not found', 'path' => $path, 'method' => $method]);
