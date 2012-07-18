<?php $this->load->view('common/header.include.php'); ?>

	<form method="post">
		<fieldset>
			<legend>Reset Password</legend>

			<?php echo $this->form_validation->display(); ?>

			<div class="row">
				<label for="user_password">Password</label>
				<input type="password" name="user_password" id="user_password" value="" />
			</div>

			<div class="row">
				<label for="user_password_confirm">Confirm Password</label>
				<input type="password" name="user_password_confirm" id="user_password_confirm" value="" />
			</div>

			<div class="row">
				<input type="submit" value="Reset Password" />
			</div>

		</fieldset>
	</form>

<?php $this->load->view('common/footer.include.php'); ?>