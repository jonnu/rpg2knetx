<?php

class Thread extends CI_Controller {

	public function __construct() {
		parent::__construct();
	}

	public function index() {
		echo 'Thread';
	}

	public function view($forum_slug, $thread_slug) {
		// Iterator with class, such as:
		// Thread_Renderer::render(thread_id/thread_slug)./Thread_Iterator
		// allows me to attach threads easily to other shit, like news posts?
		// insta-forum-based comment system.
		// thread is a list of Posts.

		$this->db->select('thread.*');
		$this->db->select('post.*');

		//$this->db->select('count(post.post_thread_id) - 1 as thread_count_replies');
		//$this->db->select('MAX(post.post_date_created) as thread_date_lastpost');

		$this->db->from('post');

		$this->db->join('thread', 'post.post_thread_id = thread.thread_id', 'left');
		$this->db->join('forum', 'forum.forum_id = thread.thread_forum_id', 'inner');
		

		$this->db->where('thread.thread_slug', $thread_slug);
		$this->db->where('forum.forum_slug', $forum_slug);

		$this->db->order_by('post.post_date_created asc');
		$test_result = $this->db->get();

		$posts = $test_result->result('Post_Object');
		var_dump($posts);
		$thread = $test_result->first_row('Full_Thread_Object');

		$thread->attach($posts);

		//var_dump($thread);
		echo 'Title: ' . $thread->getTitle();

		foreach ($thread->getPosts() as $post) {
			echo '<hr />';
			echo $post->getContent();
			
		}
	}

	public function reply() {

	}

	public function subscribe() {}
	public function unsubscribe() {}
}

class Thread_Object {

	public function getTitle() {
		return $this->thread_title;
	}

	public function getSlug() {
		return $this->thread_slug;
	}
}

// Lite thread object will only have details about last post?
class Lite_Thread_Object extends Thread_Object {}

// Full thread object will contain thread and all posts.
class Full_Thread_Object extends Thread_Object implements Countable {
	protected $posts = array();
	public function attach($posts) {
		$this->posts = $posts;
	}

	public function getPosts() {
		return new ArrayIterator($this->posts);
	}

	/* Countable */
	public function count() {
		return count($this->posts);
	}
}

class Post_Object {
	//private $t = array('post_id', 'post_thread_id', 'post_author_id', 'post_content', 'post_date_created', 'post_date_updated');

	public function __set($name, $value) {

		//echo 'Set ' . $name . ' to ' . $value . '<br />';

		//if (in_array($name, $this->t)) {
		//	$this->$name = $value;
		//}

		$underscore_position = strpos($name, '_');
		if (false === $underscore_position) {
			$clip = 'post';
		}
		else {
			$clip = substr($name, 0, $underscore_position);
		}


		switch ($clip) {
			case 'post': {
				echo 'nope... ' . $name . '<br />';
				$this->$name = $value;
				break;
			}
			default: {
				echo $name . '->' . $clip . '<br />';
				$o = ucfirst($clip) . '_Object';
				if (!isset($this->$clip) && class_exists($o)) {
					$this->$clip = new $o;
				}

				if (isset($this->$clip)) {
					$this->$clip->$name = $value;
				}

				break;
			}
		}

		//echo  . '<br />';
	}

	public function getContent() {
		return $this->post_content;
	}

	public function save() {} // ?

//    public function __get($name)
}