<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/migrations.php';
$pending=cc_pending_migrations(db());if(!$pending){echo "No pending migrations.\n";exit(0);}echo 'Pending: '.implode(', ',$pending)."\n";$done=cc_run_pending_migrations(db());foreach($done as $v)echo "Applied {$v}\n";echo "MIGRATIONS_OK\n";
