<?php

if (!function_exists('activityLogLimitText')) {
    function activityLogLimitText(string $value, int $limit): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $limit);
        }

        return substr($value, 0, $limit);
    }
}

if (!function_exists('getAuthenticatedUserId')) {
    function getAuthenticatedUserId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $userId = (int) $_SESSION['user_id'];

        return $userId > 0 ? $userId : null;
    }
}

if (!function_exists('activityLogsTableExists')) {
    function activityLogsTableExists(mysqli $conn): bool
    {
        static $cache = [];

        $cacheKey = spl_object_id($conn);
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $tableCheck = $conn->query("SHOW TABLES LIKE 'activity_logs'");
        $exists = $tableCheck && $tableCheck->num_rows > 0;

        if ($tableCheck) {
            $tableCheck->free();
        }

        $cache[$cacheKey] = $exists;

        return $exists;
    }
}

if (!function_exists('logActivity')) {
    function logActivity(mysqli $conn, ?int $userId, string $action, string $tableName, ?int $recordId = null): bool
    {
        $action = activityLogLimitText($action, 100);
        $tableName = activityLogLimitText($tableName, 50);

        if ($action === '' || $tableName === '' || !activityLogsTableExists($conn)) {
            return false;
        }

        $stmt = $conn->prepare(
            "INSERT INTO activity_logs (user_id, action, table_name, record_id) VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $normalizedUserId = ($userId !== null && $userId > 0) ? $userId : null;
        $normalizedRecordId = ($recordId !== null && $recordId > 0) ? $recordId : null;

        $stmt->bind_param("issi", $normalizedUserId, $action, $tableName, $normalizedRecordId);
        $logged = $stmt->execute();
        $stmt->close();

        return $logged;
    }
}

if (!function_exists('logCurrentUserActivity')) {
    function logCurrentUserActivity(mysqli $conn, string $action, string $tableName, ?int $recordId = null): bool
    {
        return logActivity($conn, getAuthenticatedUserId(), $action, $tableName, $recordId);
    }
}

if (!function_exists('setActivityLogActor')) {
    function setActivityLogActor(mysqli $conn, ?int $userId): bool
    {
        if ($userId === null || $userId <= 0) {
            return (bool) $conn->query("SET @actor_user_id = NULL");
        }

        return (bool) $conn->query("SET @actor_user_id = " . (int) $userId);
    }
}

if (!function_exists('setActivityLogAction')) {
    function setActivityLogAction(mysqli $conn, ?string $action): bool
    {
        if ($action === null || trim($action) === '') {
            return (bool) $conn->query("SET @activity_action = NULL");
        }

        $normalizedAction = activityLogLimitText($action, 100);
        $escapedAction = $conn->real_escape_string($normalizedAction);

        return (bool) $conn->query("SET @activity_action = '{$escapedAction}'");
    }
}

if (!function_exists('setActivityLogSuppressed')) {
    function setActivityLogSuppressed(mysqli $conn, bool $suppressed): bool
    {
        return (bool) $conn->query("SET @suppress_activity_log = " . ($suppressed ? '1' : '0'));
    }
}

if (!function_exists('setActivityLogContext')) {
    function setActivityLogContext(mysqli $conn, ?int $userId, ?string $action = null): bool
    {
        return setActivityLogActor($conn, $userId) && setActivityLogAction($conn, $action);
    }
}

if (!function_exists('setCurrentActivityLogContext')) {
    function setCurrentActivityLogContext(mysqli $conn, ?string $action = null): bool
    {
        return setActivityLogContext($conn, getAuthenticatedUserId(), $action);
    }
}
