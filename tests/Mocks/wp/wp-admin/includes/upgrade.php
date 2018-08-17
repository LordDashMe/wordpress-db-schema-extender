<?php

if (! function_exists('dbDelta')) {
    
    function dbDelta($queries)
    {
        global $result;

        array_push($result, array('queries' => $queries));
    }
}