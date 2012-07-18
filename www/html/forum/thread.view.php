<?php $this->load->view('common/header.include.php'); ?>
	
	<!-- Breadcrumb -->
	<h6>Forum &rarr; Forum Name &rarr; <?php echo $thread->title(); ?></h6>
	<!-- End Breadcrumb -->

	<!-- Thread -->
	<div>
		<?php foreach($thread->posts() as $post): ?>
		<div class="post">
			<?php echo $post->content(); ?>
		</div>
		<?php endforeach; ?>
	</div>
	<!-- End Thread -->

	<!-- Quickpost -->
	<div id="quickpost">
	
		<form method="post" action="/<?php echo $this->uri->uri_string(); ?>/reply" enctype="multipart/form-data">
			<fieldset>
				<legend>Quickpost</legend>

				<?php //echo $this->form_validation->display(); ?>

				<div class="row">
					<label for="file">txt</label>
					<textarea name="post_content" id="post_content"></textarea>
				</div>

				<div class="row">
					<input type="submit" value="Do it" />
				</div>

			</fieldset>
		</form>

	</div>
	<!-- End Quickpost -->

<?php $this->load->view('common/footer.include.php'); ?>