<?php

$route['forum/(?!search$)([^/]+)'] = 'forum/view/$1';
$route['forum/(?!search$)([^/]+)/([^/]+)'] = 'forum/thread/view/$1/$2';

