<?php $this->load->view('common/header.include.php'); ?>

	<form method="post">
		<fieldset>
			<legend>Login</legend>

			<?php echo $this->form_validation->display(); ?>

			<div class="row">
				<label for="user_email">Email</label>
				<input type="text" name="user_email" id="user_email" value="<?php echo $this->form_validation->value('user_email'); ?>" />
			</div>

			<div class="row">
				<label for="user_password">Password</label>
				<input type="password" name="user_password" id="user_password" value="" />
			</div>

			<div class="row">
				<label for="user_persistent">Remember me</label>
				<input type="checkbox" name="user_persistent" id="user_persistent"<?php echo $this->form_validation->checked('user_persistent'); ?> />
			</div>

			<div class="row">
				<input type="submit" value="Login" />
			</div>

			<?php echo anchor('account/lost', 'Lost Password'); ?>
			
		</fieldset>
	</form>

<?php $this->load->view('common/footer.include.php'); ?>