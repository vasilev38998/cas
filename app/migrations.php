<?php
declare(strict_types=1);

function cc_ensure_migration_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
function cc_index_exists(PDO $pdo,string $table,string $index): bool {
    $q=$pdo->prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=? AND index_name=?");$q->execute([$table,$index]);return (int)$q->fetchColumn()>0;
}
function cc_migrations(): array {
    return [
        '20260913_001_query_indexes'=>function(PDO $pdo): void {
            if(!cc_index_exists($pdo,'game_rounds','idx_round_created'))$pdo->exec('ALTER TABLE game_rounds ADD INDEX idx_round_created (created_at)');
            if(!cc_index_exists($pdo,'game_rounds','idx_round_game_created'))$pdo->exec('ALTER TABLE game_rounds ADD INDEX idx_round_game_created (game_key, created_at)');
            if(!cc_index_exists($pdo,'wallet_transactions','idx_wallet_user_type_ref'))$pdo->exec('ALTER TABLE wallet_transactions ADD INDEX idx_wallet_user_type_ref (user_id, type, reference)');
        },
    ];
}
function cc_applied_migrations(PDO $pdo): array {
    cc_ensure_migration_table($pdo);$rows=$pdo->query('SELECT version FROM schema_migrations ORDER BY version')->fetchAll(PDO::FETCH_COLUMN);return array_values(array_map('strval',$rows));
}
function cc_pending_migrations(PDO $pdo): array {
    $applied=array_flip(cc_applied_migrations($pdo));return array_values(array_filter(array_keys(cc_migrations()),fn($v)=>!isset($applied[$v])));
}
function cc_run_pending_migrations(PDO $pdo): array {
    $locked=(int)$pdo->query("SELECT GET_LOCK('candyclub_schema_migrations',10)")->fetchColumn();if($locked!==1)throw new RuntimeException('Не удалось получить блокировку миграций. Повторите позже.');
    try{cc_ensure_migration_table($pdo);$done=[];$migrations=cc_migrations();foreach(cc_pending_migrations($pdo) as $version){$fn=$migrations[$version]??null;if(!$fn)continue;$fn($pdo);$q=$pdo->prepare('INSERT IGNORE INTO schema_migrations(version) VALUES(?)');$q->execute([$version]);$done[]=$version;}return$done;}finally{$pdo->query("SELECT RELEASE_LOCK('candyclub_schema_migrations')")->fetchColumn();}
}
