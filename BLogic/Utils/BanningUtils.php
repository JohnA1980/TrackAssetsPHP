<?php
// NOTE: banning requires APC for caching.

$bl_use_autobanning = false;

// the ammount of times a new bad page or action may occur before it gets added to the list.
$bl_banningTolerance = 3; 
$bl_banningTimeout = 60*5;

define("AUTOBAN_ENCODING", 'UTF-8');
define("BL_IP_BANNING_TIMEOUT", 60*5);

if (! function_exists("mb_convert_encoding"))
{
	// # fail safe for installations without mbstring.
	function mb_convert_encoding($string, $from, $to) { return $string; }
}

function setBanningTimeout(int $minutes): void
{
    global $bl_banningTimeout;
    $bl_banningTimeout = 60*$minutes;
}

function banningEnabled(): bool
{
    global $bl_use_autobanning;
    return $bl_use_autobanning;
}

function setBanningEnabled(bool $on): void
{
    global $bl_use_autobanning;
    $bl_use_autobanning = $on;
}

function banningTolerance(): int
{
    global $bl_banningTolerance;
    return $bl_banningTolerance;
}

function setBanningTolerance(int $value): void
{
    global $bl_banningTolerance;
    $bl_banningTolerance = $value;
}

function _loadBanFile(): array
{
    $badPages = array();
    $badActions = array();
    $badIPs = array();
    if (file_exists(ROOT."/Persistence/banned.json")) {
        $json = file_get_contents(ROOT."/Persistence/banned.json");
        $bans = json_decode($json, true);
        $badPages = $bans["pages"];
        $badActions = $bans["actions"];
        $badIPs = safeValue($bans, "ips", array());
    } 
    
    if (extension_loaded("apc")) {
        global $bl_banningTimeout;
        apc_store(appName()."_bannedPages", $badPages, 60*60);
        apc_store(appName()."_bannedActions", $badActions, 60*60);
        apc_store(appName()."_bannedIPs", $badIPs, $bl_banningTimeout);
    }
    return array($badPages, $badActions, $badIPs);
}

function _synchroniseBanFile(?array $pages, ?array $actions, ?array $ips): void
{
    if (! $pages)
        $pages = _bannedPageList();
    if (! $actions)
        $actions = _bannedActionList();
    if (! $ips)
        $actions = _bannedIPList();

    if (extension_loaded("apc")) {
        global $bl_banningTimeout;
        apc_store(appName()."_bannedPages", $pages, 60*60);
        apc_store(appName()."_bannedActions", $actions, 60*60);
        apc_store(appName()."_bannedIPs", $actions, $bl_banningTimeout);
    }
    $bans = array("pages" => $pages, "actions" => $actions, "ips" => $ips);
    file_put_contents(ROOT."/Persistence/banned.json", json_encode($bans));
}

function _bannedPageList(): array
{
    $badPages = extension_loaded("apc") ? apc_fetch(appName()."_bannedPages") : null;
    if (! $badPages) {
        list($badPages, $unused) = _loadBanFile();
    }
    return $badPages;
}

function _bannedActionList(): array
{
    $badActions = extension_loaded("apc") ? apc_fetch(appName()."_bannedActions") : null;
    if (! $badActions) {
        list($unused, $badActions) = _loadBanFile();
    }
    return $badActions;
}

function _bannedIPList(): array
{
    $ips = extension_loaded("apc") ? apc_fetch(appName()."_bannedIPs") : null;
    if (! $ips) {
        list($unused, $unused2, $ips) = _loadBanFile();
    }
    return $ips;
}

function pageIsBanned(string $pageName): bool
{
    $pageName = htmlentities(mb_convert_encoding($pageName, 'UTF-8', 'UTF-8'), ENT_COMPAT, AUTOBAN_ENCODING, FALSE);
    $badPages = _bannedPageList();
    $attempts = safeValue($badPages, $pageName, 0);
    return ($attempts > banningTolerance());
}

function logBadPageAttempt(string $pageName): void
{
    $pageName = htmlentities(mb_convert_encoding($pageName, 'UTF-8', 'UTF-8'), ENT_COMPAT, AUTOBAN_ENCODING, FALSE);
    $badPages = _bannedPageList();
    $attempts = safeValue($badPages, $pageName, 0);
    $attempts++;
    $badPages[$pageName] = $attempts;
    _synchroniseBanFile($badPages, null, null);
}

function actionIsBanned(string $actionName): bool
{
    $actionName = htmlentities(mb_convert_encoding($actionName, 'UTF-8', 'UTF-8'), ENT_COMPAT, AUTOBAN_ENCODING, FALSE);
    $badActions = _bannedActionList();
    $attempts = safeValue($badActions, $actionName, 0);
    return ($attempts > banningTolerance());
}

function logBadActionAttempt(string $actionName): void
{
    $actionName = htmlentities(mb_convert_encoding($actionName, 'UTF-8', 'UTF-8'), ENT_COMPAT, AUTOBAN_ENCODING, FALSE);
    $badActions = _bannedActionList();
    $attempts = safeValue($badActions, $actionName, 0);
    $attempts++;
    $badActions[$actionName] = $attempts;
    _synchroniseBanFile(null, $badActions, null);
}

function ipIsBanned(string $ip): bool
{
    global $bl_banningTimeout;
    $bannedIPs = _bannedIPList();
    list($attempts, $lastAttempt) = safeValue($bannedIPs, $ip, array(0, time()));
    if (time()-$lastAttempt > $bl_banningTimeout) {
        debugln("ip ban timeout is clear, removing IP.", 1);
        unset($bannedIPs[$ip]);
        _synchroniseBanFile(null, null, $bannedIPs);
        $attempts = 0;
    }
    return ($attempts > banningTolerance());
}

function logBadIPAttempt(string $ip): void
{
    $bannedIPs = _bannedIPList();
    list($attempts, $lastAttempt) = safeValue($bannedIPs, $ip, array(0, time()));
    $attempts = $attempts+1;
    $bannedIPs[$ip] = array($attempts, time());
    _synchroniseBanFile(null, null, $bannedIPs);
}
