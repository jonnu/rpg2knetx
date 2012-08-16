<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="/assets/styles/main.css" />
    </head>
    <body>

        <div id="c">

            <nav>
                <ul>
                    <li><?php echo anchor('', 'Home'); ?></li>
                    <li><?php echo anchor('forum', 'Forum'); ?></li>
                </ul>
            </nav>

            <section>

                <?php if (false !== ($flash_message = $this->session->flashdata('core/message'))): ?>
                <div id="alert">
                    <?php echo $flash_message; ?>
                </div>
                <?php endif; ?>

