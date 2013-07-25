<?php

ini_set("session.gc_maxlifetime", SESSION_LIFETIME);
ini_set("session.gc_divisor", "1");
ini_set("session.gc_probability", "1");
ini_set("session.cookie_lifetime", SESSION_LIFETIME);
ini_set("session.cookie_domain", COOKIE_DOMAIN);
session_start();