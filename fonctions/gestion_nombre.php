<?php
    /* gestion nombre */
    function formatNumberShort($number) {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B';
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        } else {
            return $number;
        }
    }
    /* gestion 9plus */
    function gestion_9_plus($number)
    {
        if($number>9)
        {
            return "+9";
        }
    }
?>