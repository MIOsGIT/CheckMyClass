<?php
// cli-config.php (프로젝트 루트 디렉토리)

// bootstrap.php에서 EntityManager 객체를 반환하도록 했기 때문에 이를 로드합니다.
$entityManager = require __DIR__ . '/bootstrap.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// Doctrine CLI에 EntityManager를 제공합니다.
return ConsoleRunner::createHelperSet($entityManager);