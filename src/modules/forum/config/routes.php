<?php

$route['forum/(?!search$)([^/]+)'] = 'forum/view/$1';
$route['forum/(?!search$)([^/]+)/create'] = 'forum/thread/create/$1';
$route['forum/(?!search$)([^/]+)/([^/]+)'] = 'forum/thread/view/$1/$2';
$route['forum/(?!search$)([^/]+)/([^/]+)/reply'] = 'forum/thread/reply/$1/$2';
