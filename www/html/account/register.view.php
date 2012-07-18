<?php $this->load->view('common/header.include.php'); ?>

	<form method="post">
		<fieldset>
			<legend>Register</legend>

			<?php echo $this->form_validation->display(); ?>

			<div class="row">
				<label for="user_name">Username</label>
				<input type="text" name="user_name" id="user_name" value="<?php echo $this->form_validation->value('user_name'); ?>" />
			</div>

			<div class="row">
				<label for="user_email">Email</label>
				<input type="text" name="user_email" id="user_email" value="<?php echo $this->form_validation->value('user_email'); ?>" />
			</div>

			<div class="row">
				<label for="user_password">Password</label>
				<input type="password" name="user_password" id="user_password" value="" />
			</div>

			<div class="row">
				<label for="user_password_confirm">Confirm Password</label>
				<input type="password" name="user_password_confirm" id="user_password_confirm" value="" />
			</div>

			<div class="row">
				<input type="submit" value="Register" />
			</div>

		</fieldset>
	</form>

<?php $this->load->view('common/footer.include.php'); ?>