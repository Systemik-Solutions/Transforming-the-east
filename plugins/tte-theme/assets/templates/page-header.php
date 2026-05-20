<?php use Roots\Sage\Titles; ?>

<?php $page_title = get_field('page_title'); ?>

<?php if($page_title === 'no_title'): ?>
    <div class="page-header sr-only">
        <div class="container-fluid">
            <h1><?= Titles\title(); ?></h1>
        </div>
    </div>
<?php elseif($page_title === 'title_override'): ?>
    <div class="page-header">
        <div class="container-fluid">
            <h1><?php echo get_field('title_override'); ?></h1>
        </div>
    </div>
<?php else: ?>
    <div class="page-header">
        <div class="container-fluid">
            <h1><?= Titles\title(); ?></h1>
        </div>
    </div>
<?php endif; ?>
