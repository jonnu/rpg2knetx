<?php

class Forum extends CI_Controller {

    public function __construct() {

        parent::__construct();

        $this->load->model('forum_model');
    }

    public function index() {

        $forums = $this->forum->tree();
        $this->load->view('forum/index.view.php', array('forums' => $forums));
    }

    public function view($forum_slug) {

        if (!$forum = $this->forum->retrieve($forum_slug)) {
            return show_404();
        }

        $this->load->view('forum/forum.view.php', array('forum' => $forum));
    }


}