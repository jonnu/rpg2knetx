<?php $this->load->view('common/header.include.php'); ?>
    
    <h2>Dashboard</h2>

    <form method="post" action="/account" enctype="multipart/form-data">
        <fieldset>
            <legend>Avatar</legend>

            <?php echo $this->form_validation->display(); ?>

            <div class="row">
                <label for="file">File</label>
                <input type="file" name="file" id="file" />
            </div>

            <div class="row">
                <input type="submit" value="Do it" />
            </div>

            <input type="hidden" name="file_nonce" value="abc123" />

        </fieldset>
    </form>

<?php $this->load->view('common/footer.include.php'); ?>