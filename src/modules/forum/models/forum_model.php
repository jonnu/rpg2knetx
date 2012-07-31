<?php

class Forum_Model extends Model_NestedSet {

    public function __construct() {
        parent::__construct();
    }

    // @todo rename?
    public function tree() {
        return parent::retrieve_nested(null, 0, 1);
    }


    public function create() {

    }

    public function retrieve($slug) {

        $forum = parent::retrieve($slug);

        return $forum;
    }

    public function update() {

    }

    public function delete($id) {

    }


}


class Forum_Object extends Nestable_Object {

    public function title() {
        return $this->forum_title;
    }

    public function slug() {
        return $this->forum_slug;
    }

    public function uri() {

        if (isset($this->forum_uri)) {
        return site_url(array('forum', $this->forum_uri));
        }

        return site_url(array('forum', $this->slug()));
    }

    public function description() {
        return $this->forum_description;
    }


}
