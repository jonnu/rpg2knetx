<?php $this->load->view('common/header.include.php'); ?>

    <h1>RPG2KNET</h1>

    <?php if ($this->user->authenticated()): ?>
    Logged in as <mark><?php echo anchor('account/display/' . $this->user->name(true), $this->user->name()); ?></mark><br />
    (<?php echo anchor('account/logout', 'Log Out'); ?>)
    <?php else: ?>
    Not logged in.<br />
    (<?php echo anchor('account/register', 'Register'); ?> | 
     <?php echo anchor('account/login', 'Log In'); ?>)
    <?php endif; ?>

<?php $this->load->view('common/footer.include.php'); ?>