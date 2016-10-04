#!/usr/bin/php -q
<?php

$data_dir = "/tmp/picasa_extract";

require_once ('PicasaFind.php');
require_once ('PicasaIni.php');
require_once ('ImageProcessor.php');

list ($me, $path) = $argv;

if (!is_dir ($data_dir))
{
    mkdir ($data_dir, 0775);
}

@mkdir ($data_dir . "/albums", 0775);
@mkdir ($data_dir . "/star_albums", 0775);
@mkdir ($data_dir . "/tag_albums", 0775);

$pi = new PicasaIni ($data_dir, "$data_dir/picasa_ini.txt");
$ip = new ImageProcessor ($data_dir, "$data_dir/image_processor.txt");

$pe = new PicasaFind ($path, $pi, $ip, "$data_dir/picasa_find.txt");
$pe->run ();
