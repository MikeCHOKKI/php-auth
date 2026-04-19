<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\AuthenticationService;
use App\Application\Service\AuthorizationService;
use App\Application\Service\TokenRotationService;
use App\Infrastructure\Persistence\PostgreSql\PostgreSqlRefreshTokenRepository;
use App\Infrastructure\Persistence\PostgreSql\PostgreSqlUserRepository;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\Rs256JwtSigner;
use App\Infrastructure\Web\Controller\AuthController;
use App\Infrastructure\Web\Controller\UserController;
use App\Infrastructure\Web\Middleware\JwtMiddleware;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Database connection
$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s',
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_PORT'] ?? '5432',
    $_ENV['DB_DATABASE'] ?? 'php_auth'
);

$pdo = new PDO(
    $dsn,
    $_ENV['DB_USERNAME'] ?? 'postgres',
    $_ENV['DB_PASSWORD'] ?? 'secret',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

// Security services
$passwordHasher = new BcryptPasswordHasher();
$jwtSigner = new Rs256JwtSigner(
    $_ENV['JWT_PRIVATE_KEY_PATH'] ?? __DIR__ . '/../keys/private.pem',
    $_ENV['JWT_PUBLIC_KEY_PATH'] ?? __DIR__ . '/../keys/public.pem'
);

// Repositories
$userRepository = new PostgreSqlUserRepository($pdo);
$refreshTokenRepository = new PostgreSqlRefreshTokenRepository($pdo);

// Application services
$tokenRotationService = new TokenRotationService($refreshTokenRepository);
$authService = new AuthenticationService(
    $userRepository,
    $refreshTokenRepository,
    $passwordHasher,
    $jwtSigner,
    $tokenRotationService,
    (int)($_ENV['JWT_ACCESS_TOKEN_TTL'] ?? 900),
    (int)($_ENV['JWT_REFRESH_TOKEN_TTL'] ?? 604800)
);
$authzService = new AuthorizationService($userRepository);

// Controllers
$authController = new AuthController($authService);
$userController = new UserController($authzService);

// Middleware
$jwtMiddleware = new JwtMiddleware($authService, ['/api/auth/login', '/api/auth/refresh']);

// Routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

// Apply JWT middleware (except for public routes)
$jwtMiddleware->handle();

// Route matching
if ($path === '/api/auth/login' && $method === 'POST') {
    $authController->login();
} elseif ($path === '/api/auth/refresh' && $method === 'POST') {
    $authController->refresh();
} elseif ($path === '/api/auth/logout' && $method === 'POST') {
    $authController->logout();
} elseif ($path === '/api/me' && $method === 'GET') {
    $userController->me();
} elseif ($path === '/api/permissions' && $method === 'GET') {
    $userController->permissions();
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
}