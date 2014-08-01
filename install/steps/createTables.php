<?php

function CreateTables()
{
    $settings = $_SESSION['asi_install']['mysql'];
    $prefix = $settings["prefix"];
    if (DB::connect($settings['server'], $settings['login'], $settings["password"], $settings["name"]))
    {
        $sql = "DROP TABLE IF EXISTS `" . $prefix . "assets`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "assets` (
            `id` int(10) unsigned NOT NULL,
            `path` text NOT NULL,
            `title` text NOT NULL,
            `description` text,
            `version` text,
            `version_id` int(10) unsigned DEFAULT NULL,
            `publishnotes` text,
            `min_unity_version` text,
            `size` int(11) DEFAULT '0',
            `pubdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `price` int(10) unsigned DEFAULT NULL,
            `short_url` text,
            `category_label` text,
            `category_short_url` text,
            `keyimage_small` text,
            `keyimage_icon` text,
            `keyimage_big` text,
            `rating_count` int(10) unsigned DEFAULT '0',
            `rating_average` int(10) unsigned DEFAULT '0',
            `hotness` float DEFAULT '0',
            `first_sale` date,
            UNIQUE KEY `id` (`id`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "charges`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "charges` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `asset_id` int(10) unsigned NOT NULL,
            `count` int(10) unsigned NOT NULL,
            `offset` int(10) unsigned NOT NULL,
            `price` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `time` (`time`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "events`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "events` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(16) NOT NULL,
            `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `asset_id` int(11) NOT NULL,
            `info` text,
            PRIMARY KEY (`id`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "informer`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "informer` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` text NOT NULL,
            `time` int(11) NOT NULL,
            `asset` text NOT NULL,
            `params` text NOT NULL,
            PRIMARY KEY (`id`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "months`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "months` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `asset_id` int(11) NOT NULL,
            `month` int(11) NOT NULL,
            `sales` int(11) NOT NULL,
            `refundings` int(11) NOT NULL,
            `charges` int(11) NOT NULL,
            `total` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `AID_Month` (`asset_id`,`month`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "publisher`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "publisher` (
            `id` int(10) unsigned NOT NULL,
            `name` text,
            `description` text,
            `short_url` text,
            `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
            `rating_average` int(10) unsigned NOT NULL DEFAULT '0',
            `url` text,
            `support_url` text,
            `support_email` text,
            `keyimage_small` text,
            `keyimage_big` text,
            `invoice_key` text,
            `first_sale` date,
            `payout_cut` float NOT NULL DEFAULT '0.7',
            `lastSendTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `id` (`id`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "ratings`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "ratings` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `asset_id` int(10) unsigned NOT NULL,
            `count` int(10) unsigned NOT NULL DEFAULT '0',
            `offset` int(10) unsigned NOT NULL DEFAULT '0',
            `oldrating` int(11) DEFAULT '0',
            `newrating` int(11) DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `time` (`time`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "refundings`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "refundings` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `asset_id` int(10) unsigned NOT NULL,
            `count` int(10) unsigned NOT NULL,
            `offset` int(10) unsigned NOT NULL,
            `price` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `time` (`time`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "reviews`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "reviews` (
            `id` int(10) unsigned NOT NULL,
            `asset_id` int(11) NOT NULL,
            `date` date NOT NULL,
            `subject` text NOT NULL,
            `full` text NOT NULL,
            `version` text NOT NULL,
            `rating` tinyint(3) unsigned DEFAULT '0',
            `user_id` int(10) unsigned NOT NULL,
            `user_name` text NOT NULL,
            `helpful_count` int(11) DEFAULT '0',
            `helpful_score` int(11) DEFAULT '0',
            `reply_subject` text,
            `reply_full` text,
            `reply_date` date DEFAULT NULL,
            UNIQUE KEY `id` (`id`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "sales`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "sales` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `asset_id` int(10) unsigned NOT NULL,
            `count` int(10) unsigned NOT NULL,
            `offset` int(10) unsigned NOT NULL,
            `price` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `aid_time` (`asset_id`,`time`),
            KEY `time` (`time`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "settings`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "settings` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `mail_to` text,
            `mail_from` text,
            `informer_period` int(11) NOT NULL,
            `assetstore_server` text NOT NULL,
            `assetstore_publisher_server` text NOT NULL,
            `salt` text NOT NULL,
            `token` text NOT NULL,
            `install_update` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "tasks`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "tasks` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `priority` tinyint(3) unsigned NOT NULL,
            `task` text NOT NULL,
            `asset_id` int(10) unsigned NOT NULL,
            `count_failed` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `time_priority` (`time`,`priority`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "updates`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "updates` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `version` varchar(16) NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `changelog` text NOT NULL,
            `filename` text NOT NULL,
            PRIMARY KEY (`id`)
        );
        DROP TABLE IF EXISTS `" . $prefix . "users`;
        CREATE TABLE IF NOT EXISTS `" . $prefix . "users` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `login` text NOT NULL,
            `pass` text NOT NULL,
            `access` text NOT NULL,
            PRIMARY KEY (`id`)
        );";
        
        return DB::multi_query($sql);
    }
}