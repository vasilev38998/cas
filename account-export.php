<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
$user=require_login();$uid=(int)$user['id'];cc_rate_limit('export:'.$uid,5,3600);
$q=db()->prepare('SELECT username,email,balance_kopecks,created_at,last_login_at FROM users WHERE id=? LIMIT 1');$q->execute([$uid]);$profile=$q->fetch();
$q=db()->prepare('SELECT type,amount_kopecks,balance_after_kopecks,reference,metadata_json,created_at FROM wallet_transactions WHERE user_id=? ORDER BY id');$q->execute([$uid]);$wallet=$q->fetchAll();
$q=db()->prepare('SELECT id,game_key,mode,bet_kopecks,cost_kopecks,win_kopecks,balance_before_kopecks,balance_after_kopecks,result_json,created_at FROM game_rounds WHERE user_id=? ORDER BY id');$q->execute([$uid]);$rounds=$q->fetchAll();
$q=db()->prepare('SELECT game_key,free_spins,storm_charge,multiplier_map_json,updated_at FROM user_game_states WHERE user_id=? ORDER BY game_key');$q->execute([$uid]);$states=$q->fetchAll();
header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename="candyclub-data-'.date('Ymd-His').'.json"');header('Cache-Control: no-store');echo json_encode(['exported_at'=>gmdate('c'),'profile'=>$profile,'wallet_transactions'=>$wallet,'game_rounds'=>$rounds,'game_states'=>$states],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
