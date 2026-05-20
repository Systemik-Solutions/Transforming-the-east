<header class="site-header headroom">
    <!-- <div class="topbar d-none d-lg-block">
        <div class="container-fluid d-flex px-lg-0">
            <?php if (has_nav_menu('topbar_navigation')) : ?>
                <?php
                wp_nav_menu(array(
                    'theme_location'    => 'topbar_navigation',
                    'depth'             => 2,
                    'container_class'   => 'ml-auto',
                    'menu_class'        => 'topbar-nav nav',
                    'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                    'walker'            => new WP_Bootstrap_Navwalker(),
                ));
                ?>
            <?php endif; ?>
        </div>
    </div>  -->

    <div class="midbar">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2" style="display: flex;align-items: center;">
                    <a class="midbar-brand" href="https://sydney.edu.au/">
                        <img class="img-fluid d-none d-lg-block" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-b.png" alt="<?php bloginfo('name'); ?>">
                        <img class="img-fluid d-block d-lg-none" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-w.png" alt="<?php bloginfo('name'); ?>">
                    </a> <!--/ .midbar-brand -->
                </div>
                <div class="col-md-8 main-brand">
                    <a class="main-logo" href="/">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/image/textus_icon_logo.png">
                        <div style="padding-left: 3%;">
                            <h3>Transforming the East</h1>
                            <h5>Jesuit Translations of the Confucian Classics</h2>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">

                </div>
            </div>
        </div>
    </div> <!--/ .midbar -->

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">

            <?php if (has_nav_menu('primary_navigation')) : ?>
                <button id="jsNavbarToggler" class="navbar-toggler mr-auto collapsed" type="button" data-toggle="collapse" data-target="#jsPrimaryMenu" aria-controls="jsPrimaryMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            <?php endif; ?>


            <div id="jsPrimaryMenu" class="collapse navbar-collapse" style="justify-content: center;">
                <?php if (has_nav_menu('primary_navigation')) : ?>
                    <?php
                    wp_nav_menu(array(
                        'theme_location'    => 'primary_navigation',
                        'depth'             => 2,
                        'container_class'   => '',
                        'menu_class'        => 'primary-nav navbar-nav',
                        'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                        'walker'            => new WP_Bootstrap_Navwalker(),
                    ));
                    ?>
                <?php endif; ?>
                <div class="d-block d-lg-none">
                    <?php if (has_nav_menu('topbar_navigation')) : ?>
                        <?php
                        wp_nav_menu(array(
                            'theme_location'    => 'topbar_navigation',
                            'depth'             => 2,
                            'container_class'   => 'ml-auto',
                            'menu_class'        => 'navbar-nav navbar-nav_bottom nav',
                            'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                            'walker'            => new WP_Bootstrap_Navwalker(),
                        ));
                        ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<?php get_template_part('templates/section-hero'); ?>
<?php get_template_part('templates/breadcrumbs'); ?>