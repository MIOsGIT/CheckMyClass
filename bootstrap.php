<?php
// bootstrap.php

use Doctrine\DBAL\DriverManager; 
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Dotenv\Dotenv;

// Composer Autoload 로드
require_once __DIR__ . '/vendor/autoload.php';

// 1. 환경 변수(.env) 로드
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// 2. Doctrine 설정 구성
$isDevMode = true;
$paths = [__DIR__.'/db/Entity']; 

// Attribute 메타데이터 설정 사용
$config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);

// 3. 데이터베이스 연결 정보
$connectionParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'user'     => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'dbname'   => $_ENV['DB_NAME'] ?? 'checkmyclass',
];
// 4-1. DB 연결 생성 (DriverManager 사용)
$connection = DriverManager::getConnection($connectionParams, $config);

// 4-2. EntityManager 직접 생성 (new 키워드 사용)
$entityManager = new EntityManager($connection, $config);

return $entityManager;