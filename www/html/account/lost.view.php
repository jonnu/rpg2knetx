<?php $this->load->view('common/header.include.php'); ?>

	<form method="post">
		<fieldset>
			<legend>Lost Password</legend>

			<?php echo $this->form_validation->display(); ?>

			<div class="row">
				<label for="user_email">Email</label>
				<input type="text" name="user_email" id="user_email" value="<?php echo $this->form_validation->value('user_email'); ?>" />
			</div>

			<div class="row">
				<input type="submit" value="Login" />
			</div>

		</fieldset>
	</form>

<?php $this->load->view('common/footer.include.php'); ?>