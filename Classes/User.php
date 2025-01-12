<?php

class User
{
    const RANK_USER = 1, RANK_OBSERVER = 2, RANK_MODERATOR = 4, RANK_ADMIN = 8, RANK_DEVELOPER = 16;
    public static function IsLoggedIn()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        return isset($_SESSION['loggedIn']);
    }

    public static function IsAdmin()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        return isset($_SESSION['Admin']);
    }

    public static function AdminRank()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        return (int)$_SESSION['Rank'];
    }
}