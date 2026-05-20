<?php get_template_part('templates/section-contacts'); ?>

<footer class="site-footer">
    <div class="container-fluid">
        <div class="footer-head">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <a class="footer-head__brand" href="https://sydney.edu.au/">
                        <img class="img-fluid" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-b.png" alt="<?php bloginfo('name'); ?>">
                    </a> <!--/ .midbar-brand -->
                </div>
                <?php if(get_field('footer_tagline', 'option')): ?>
                <div class="col-lg-8 mb-5">
                  <div class="footer-head__tagline"><?php echo get_field('footer_tagline', 'option'); ?></div>
                </div>
                <?php else: ?>
                  <div class="footer-head__tagline">Leadership for good starts here</div>
                <?php endif; ?>
            </div>
        </div> <!--/ .footer-head -->

        <div class="row mb-3">
            <div class="col-sm-1 d-none d-sm-block">
                <?php $social_media_links = get_field('social_media_links', 'option'); ?>
                <div class="footer-links__social">
                    <ul class="list-unstyled mb-0 px-1">
                        <?php if($social_media_links['twitter']): ?>
                          <li><a href="<?php echo $social_media_links['twitter']; ?>"><i class="fa fa-twitter" aria-hidden="true"></i><span class="sr-only">Twitter</span></a></li>
                        <?php else: ?>
                          <li><a href="https://twitter.com/sydney_uni"><i class="fa fa-twitter" aria-hidden="true"></i><span class="sr-only">Twitter</span></a></li>
                        <?php endif; ?>

                        <?php if($social_media_links['facebook']): ?>
                          <li><a href="<?php echo $social_media_links['facebook']; ?>"><i class="fa fa-facebook-official" aria-hidden="true"></i><span class="sr-only">Facebook</span></a></li>
                        <?php else: ?>
                          <li><a href="https://www.facebook.com/sydneyuni"><i class="fa fa-facebook-official" aria-hidden="true"></i><span class="sr-only">Facebook</span></a></li>
                        <?php endif; ?>

                        <?php if($social_media_links['instagram']): ?>
                          <li><a href="<?php echo $social_media_links['instagram']; ?>"><i class="fa fa-instagram" aria-hidden="true"></i><span class="sr-only">Instagram</span></a></li>
                        <?php else: ?>
                          <li><a href="https://instagram.com/sydney_uni/"><i class="fa fa-instagram" aria-hidden="true"></i><span class="sr-only">Instagram</span></a></li>
                        <?php endif; ?>

                        <?php if($social_media_links['youtube']): ?>
                          <li><a href="<?php echo $social_media_links['youtube']; ?>"><i class="fa fa-youtube" aria-hidden="true"></i><span class="sr-only">Youtube</span></a></li>
                        <?php else: ?>
                          <li><a href="https://www.youtube.com/user/uniofsydney"><i class="fa fa-youtube" aria-hidden="true"></i><span class="sr-only">Youtube</span></a></li>
                        <?php endif; ?>
                    </ul>
                    <label>Follow us_</label>
                </div>
            </div>
            <div class="col-sm-11">
                <div class="footer-links mb-4">
                    <div class="row">
                        <div class="col-6 mb-5 mb-sm-0 col-sm-3">
                            <div>
                                <?php dynamic_sidebar('sidebar-footer-1'); ?>
                            </div>
                        </div>
                        <div class="col-6 mb-5 mb-sm-0 col-sm-3 d-sm-flex justify-content-center">
                            <div>
                                <?php dynamic_sidebar('sidebar-footer-2'); ?>
                            </div>
                        </div>
                        <div class="col-6 mb-5 mb-sm-0 col-sm-3 d-sm-flex justify-content-center">
                            <div>
                                <?php dynamic_sidebar('sidebar-footer-3'); ?>
                            </div>
                        </div>
                        <div class="col-6 mb-5 mb-sm-0 col-sm-3 d-sm-flex justify-content-end">
                            <div>
                                <?php dynamic_sidebar('sidebar-footer-4'); ?>
                            </div>
                        </div>
                    </div>
                </div> <!--/ .footer-links -->

                <div class="footer-logos mb-4">
                    <div class="row d-inline-flex align-items-center">
                        <div class="col">
                            <a href="https://go8.edu.au/" class="group-of-eight-logo" target="_blank">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/group-of-eight-logo.png" alt="/group-of-eight" class="img-fluid">
                            </a>
                        </div>
                    </div>
                </div> <!--/ .footer-logos -->

                <div class="footer-copyright mb-4">
                    <div class="row">
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-6 d-block d-sm-none">
                                    <?php $social_media_links = get_field('social_media_links', 'option'); ?>
                                    <div class="footer-links__social">
                                        <label class="pl-3">Follow us_</label>
                                        <ul class="list-inline pl-5">
                                            <?php if($social_media_links['twitter']): ?>
                                              <li><a href="<?php echo $social_media_links['twitter']; ?>"><i class="fa fa-twitter" aria-hidden="true"></i><span class="sr-only">Twitter</span></a></li>
                                            <?php else: ?>
                                              <li><a href="https://twitter.com/sydney_uni"><i class="fa fa-twitter" aria-hidden="true"></i><span class="sr-only">Twitter</span></a></li>
                                            <?php endif; ?>

                                            <?php if($social_media_links['facebook']): ?>
                                              <li><a href="<?php echo $social_media_links['facebook']; ?>"><i class="fa fa-facebook-official" aria-hidden="true"></i><span class="sr-only">Facebook</span></a></li>
                                            <?php else: ?>
                                              <li><a href="https://www.facebook.com/sydneyuni"><i class="fa fa-facebook-official" aria-hidden="true"></i><span class="sr-only">Facebook</span></a></li>
                                            <?php endif; ?>

                                            <?php if($social_media_links['instagram']): ?>
                                              <li><a href="<?php echo $social_media_links['instagram']; ?>"><i class="fa fa-instagram" aria-hidden="true"></i><span class="sr-only">Instagram</span></a></li>
                                            <?php else: ?>
                                              <li><a href="https://instagram.com/sydney_uni/"><i class="fa fa-instagram" aria-hidden="true"></i><span class="sr-only">Instagram</span></a></li>
                                            <?php endif; ?>

                                            <?php if($social_media_links['youtube']): ?>
                                              <li><a href="<?php echo $social_media_links['youtube']; ?>"><i class="fa fa-youtube" aria-hidden="true"></i><span class="sr-only">Youtube</span></a></li>
                                            <?php else: ?>
                                              <li><a href="https://www.youtube.com/user/uniofsydney"><i class="fa fa-youtube" aria-hidden="true"></i><span class="sr-only">Youtube</span></a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-12 p-sm-0">
                                    <?php if (has_nav_menu('footer_navigation')) : ?>
                                        <?php
                                            wp_nav_menu( array(
                                                'theme_location'    => 'footer_navigation',
                                                'depth'             => 1,
                                                'container_class'   => '',
                                                'menu_class'        => 'nav flex-column flex-sm-row',
                                                'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                                                'walker'            => new WP_Bootstrap_Navwalker(),
                                            ) );
                                        ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 ml-auto d-flex justify-lg-content-end">
                          <div class="footer-copyright__act">
                            <?php if($registration_numbers = get_field('registration_numbers', 'option')): ?>
                                <p>ABN: <?php echo $registration_numbers['abn']; ?><br>
                                CRICOS Number: <?php echo $registration_numbers['cricos_number']; ?><br>
                                TEQSA: <?php echo $registration_numbers['teqsa']; ?>
                                </p>
                            <?php else: ?>
                              <p>ABN: 15 211 513 464<br>
                                CRICOS Number: 00026A<br>
                                TEQSA: PRV12057</p>
                            <?php endif; ?>
                          </div>
                        </div>
                    </div>
                </div> <!--/ .footer-copyright -->
            </div>
        </div>
    </div>
</footer>
