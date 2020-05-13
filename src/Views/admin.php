<div class="wrap">

    <div id="icon-themes" class="icon32"></div>
    <h2>Q&A Options</h2>
    <?php settings_errors(); ?>

    <h2 class="nav-tab-wrapper">
        <a href="#" class="nav-tab">General Options</a>
        <a href="#" class="nav-tab">Import/Export data</a>
    </h2>

    <form method="post" action="options.php">

        <?php //settings_fields( 'sandbox_theme_display_options' ); 
        ?>
        <?php //do_settings_sections( 'sandbox_theme_display_options' ); 
        ?>

        <?php //settings_fields( 'sandbox_theme_social_options' ); 
        ?>
        <?php //do_settings_sections( 'sandbox_theme_social_options' ); 
        ?>

        <?php settings_fields('nextButtonSettingsPage');
        ?>

        <?php do_settings_sections('nextButtonSettingsPage'); ?>



        <?php submit_button(); ?>

    </form>

</div><!-- /.wrap -->