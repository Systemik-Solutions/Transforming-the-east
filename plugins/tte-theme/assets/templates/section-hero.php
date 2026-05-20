<?php if(is_home()): ?>
    <?php $hero_banner = get_field('hero_banner', get_option('page_for_posts')); ?>
<?php else: ?>
    <?php $hero_banner = get_field('hero_banner'); ?>
<?php endif; ?>

<?php $hero_banner_switch = get_field('hero_banner_switch'); ?>

<?php if(($hero_banner_switch === 'with_hero_banner') && $hero_banner): ?>
<div class="container-fluid">
    <div class="hero">
        <div class="hero-banner">
            <?php if($hero_banner['hero_banner_image']): ?>
                <figure class="hero-banner__image bg-image" style="background-image: url(<?php echo $hero_banner['hero_banner_image']['sizes']['large']; ?>); "></figure>
            <?php endif; ?>

            <?php if($hero_banner['hero_banner_copy']): ?>
                <?php if($hero_banner['hero_banner_copy']['hero_banner_title'] || $hero_banner['hero_banner_copy']['hero_banner_text']): ?>
                    <div class="row h-100">
                        <div class="col-lg-5">
                            <div class="hero-banner__copy d-flex flex-column justify-content-between">
                                <h3 class="hero-banner__title"><?php echo $hero_banner['hero_banner_copy']['hero_banner_title']; ?></h3>
                                <p class="hero-banner__text"><?php echo $hero_banner['hero_banner_copy']['hero_banner_text']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
