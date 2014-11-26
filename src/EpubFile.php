<?php

namespace Icontact\BooXtreamClient;

use GuzzleHttp\Post\PostFile;

class EpubFile extends PostFile {
    public function __construct($name,$content) {
        parent::__construct($name, $content);
    }
}