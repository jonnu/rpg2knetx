<?php

class Forum extends CI_Controller {

	public function __construct() {

		parent::__construct();

		$this->load->model('forum_model');
	}

	public function index() {
		
		

		//$this->forum->temp_getlist();

		$this->load->view('forum/index.view.php');
	}


	/*
	
	- display boards /forum
	- display section /forum/general
	- display threads /forum/tokyo-central
	- display threads page /forum/tokyo-central/page/2
	- display thread /forum/tokyo-central/thread-title
	- display thread by id (?) /forum/tokyo-central/400
	- display thread page /forum/tokyo-central/thread-title/page/2
	- display post /forum/tokyo-central/thread-title/post/123456
	
	Board - contains ordered Forums (in sections maybe).
	Forum - contains many Threads, + title, + date
	Thread - contains many Posts, + title, + date
	Post - post item.


	[Board] -> list of [Section]s, containing individual [Forum]s.
	Within a [Forum] there are pages of [Thread]s.  Each [Thread] is a list of [Post]s.

	Board->Section->Forum->Thread->Post.

	- main entity is a post.
	- thread is loose glue that groups posts, & has a title.  a 'thread title', & status.
	- forum is again loose glue that groups threads, with a title & status, plus order (nested set).
	- forums and sub-forums are same, just nested.  you can have threads at the node or the leaf.
	- section is loose glue again, grouping forums.  it has a title and a position (perhaps NS again?)  may be overkill.
	- board - list of sections (if we use them), or list of forums.  may be nice to group boards actually.
	
	- optional:
		- a section is actually a forum, but with no permission to post to it?

	- permissions:
		- user, group.
		
		FORUM_POST_VIEW			- 
		FORUM_POST_CREATE		- create a post
		FORUM_POST_DELETE_SELF	- delete a post they have created

		FORUM_MODERATE			- can moderate a forum?
		FORUM_ADMINISTRATE		- can administrate a forum?

	*/
}