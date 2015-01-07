<?php
date_default_timezone_set("Europe/Berlin");
mb_internal_encoding('UTF-8');


abstract class Config{
    const PATH = 'http://localhost/agamondo/';

    const API_HASH = 'kCm3xuYupRpa6k0x6LPK';
    const NOCSRF_SESSION_VARIABLE = 'csrf_token';
    const NOCSRF_TOKEN_TIMEOUT = 1800;

    const ERROR_EMAIL = 'ati_b@wp.pl';


    const DB_HOST = 'localhost';
    const DB_PORT = 3306;
    const DB_USER = 'root';
    const DB_PASSWORD = '';
    const DB_NAME = 'agamondo';
}