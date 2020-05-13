<div class="wrap">

    <div id="icon-themes" class="icon32"></div>
    <h2>Q&A Memberpress Connector</h2>
    <?php settings_errors(); ?>



    <form method="post" action="options.php">

        <?php settings_fields('memberpress');
        ?>
        <?php do_settings_sections('memberpress');
        ?>


        <?php submit_button(); ?>

    </form>

</div><!-- /.wrap -->