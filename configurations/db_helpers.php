<?php

if (!function_exists('flushStoredProcedureResults')) {
    function flushStoredProcedureResults(mysqli $conn): void
    {
        while ($conn->more_results()) {
            $conn->next_result();
            $extraResult = $conn->store_result();

            if ($extraResult instanceof mysqli_result) {
                $extraResult->free();
            }
        }
    }
}
