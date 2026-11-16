<?php

/**
 * ============================================================================
 * TRAVEL PLANNER - APACHE SOLR 9 INITIALIZATION & FULL SYNCHRONIZATION
 * ============================================================================
 *
 * File khoi chay chinh (Entrypoint) cho toan bo cau hinh Schema va dong bo
 * tim kiem Apache Solr 9 trong du an Travel Planner.
 *
 * Cach chay:
 *   php run.php
 *   hoac:
 *   docker exec travel-planner-app php run.php
 */

$syncScript = __DIR__ . '/packages/search/Sync/run.php';

if (!file_exists($syncScript)) {
    echo "[LOI] Khong tim thay file thuc thi cau hinh tai {$syncScript}\n";
    exit(1);
}

require $syncScript;
