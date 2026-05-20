<?php
/**
 * Template Name: Template - Repeatable Sections
 */
?>
<?php get_template_part('templates/page', 'header'); ?>

<?php if( have_rows('repeatable_sections') ): ?>
    <?php while ( have_rows('repeatable_sections') ) : the_row(); ?>

        <?php if( get_row_layout() == '1_column_content' ): ?>

            <section class="col1-content">
                <div class="container-fluid my-4">
                    <?php the_sub_field('col1_copy'); ?>
                </div>
            </section>

        <?php elseif( get_row_layout() == '2_column_content' ): ?>

            <section class="col2-content">
                <div class="container-fluid my-4">
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <?php the_sub_field('col2_left_copy'); ?>
                        </div>
                        <div class="col-lg-6">
                            <?php the_sub_field('col2_right_copy'); ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif( get_row_layout() == '3_column_content' ):  ?>

            <section class="col2-content">
                <div class="container-fluid my-4">
                    <div class="row">
                        <div class="col-lg-4 mb-4 mb-lg-0">
                            <?php the_sub_field('col3_left_copy'); ?>
                        </div>
                        <div class="col-lg-4 mb-4 mb-lg-0">
                            <?php the_sub_field('col3_middle_copy'); ?>
                        </div>
                        <div class="col-lg-4">
                            <?php the_sub_field('col3_right_copy'); ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif( get_row_layout() == '2_column_left_text_right_image' ):  ?>

            <section class="col2-content">
                <div class="container-fluid my-4">
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <?php the_sub_field('col2_left_text'); ?>
                        </div>
                        <div class="col-lg-6">
                            <?php if($col2_right_image = get_sub_field('col2_right_image')): ?>
                                <img src="<?php echo $col2_right_image['sizes']['large']; ?>" alt="<?php echo $col2_right_image['alt']; ?>" class="img-fluid w-100">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif( get_row_layout() == '2_column_left_image_right_text' ):  ?>

            <section class="col2-content">
                <div class="container-fluid my-4">
                    <div class="row">
                        <div class="col-lg-6 order-lg-2">
                            <?php the_sub_field('col2_right_text'); ?>
                        </div>
                        <div class="col-lg-6 mb-4 mb-lg-0 order-lg-1">
                            <?php if($col2_left_image = get_sub_field('col2_left_image')): ?>
                                <img src="<?php echo $col2_left_image['sizes']['large']; ?>" alt="<?php echo $col2_left_image['alt']; ?>" class="img-fluid w-100">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif( get_row_layout() == 'table_content' ): ?>

            <section class="table-content">
                <div class="container-fluid my-4">
                    <?php $table = get_sub_field( 'copy_table' ); ?>
                    <?php if ( ! empty ( $table ) ): ?>
                        <table border="0">
                            <?php if ( ! empty( $table['caption'] ) ): ?>
                                <caption><?php echo $table['caption']; ?></caption>
                            <?php endif; ?>

                            <?php if ( ! empty( $table['header'] ) ): ?>
                                <thead>
                                    <tr>
                                        <?php foreach ( $table['header'] as $th ): ?>
                                            <th><?php echo $th['c']; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                            <?php endif; ?>

                            <tbody>
                                <?php foreach ( $table['body'] as $tr ): ?>
                                    <tr>
                                        <?php foreach ( $tr as $td ): ?>
                                            <td><?php echo $td['c']; ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>

            <?php elseif( get_row_layout() == '2_column_-_left_table_right_image' ): ?>
	
             <section class="col2-content">
                <div class="container-fluid my-4">
                    <div class="row">
                        <div class="col-lg-6 order-lg-1">
						<?php
						$table = get_sub_field( 'col2_left_table' );
					
						if (!empty($table)) {							
							echo '<table border="0" class="center">';
								if ( ! empty( $table['caption'] ) ) {
									echo '<caption>' . $table['caption'] . '</caption>';
								}

								if ( ! empty( $table['header'] ) ) {
									echo '<thead>';
										echo '<tr>';
											foreach ( $table['header'] as $th ) {
												echo '<th>';
													echo $th['c'];
												echo '</th>';
											}
										echo '</tr>';
									echo '</thead>';
								}

								echo '<tbody>';
									foreach ( $table['body'] as $tr ) {
										echo '<tr>';
											foreach ( $tr as $td ) {
												echo '<td>';
													echo $td['c'];
												echo '</td>';
											}
										echo '</tr>';
									}
								echo '</tbody>';
							echo '</table>';
						}
						?>
                        </div>
                        <div class="col-lg-6 mb-4 mb-lg-0 order-lg-2">
                            <?php if($col2_right_image = get_sub_field('col2_right_image')): ?>							
								<img src="<?php echo $col2_right_image['sizes']['large']; ?>" alt="<?php echo $col2_right_image['alt']; ?>" class="img-fluid w-100">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

			<?php elseif( get_row_layout() == '2_column_-_left_image_right_table' ): ?>

            <section class="col2-content">
                <div class="container-fluid my-4">
                    <div class="row">                        
                        <div class="col-lg-6 mb-4 mb-lg-0 order-lg-1">
                            <?php if($col2_left_image = get_sub_field('col2_left_image')): ?>
                                <img src="<?php echo $col2_left_image['sizes']['large']; ?>" alt="<?php echo $col2_left_image['alt']; ?>" class="img-fluid w-100">
                            <?php endif; ?>
                        </div>
						<div class="col-lg-6 order-lg-2">
						<?php
						$table = get_sub_field( 'col2_right_table' );							
						if ( ! empty ( $table ) ) {
							echo '<table border="0" class="center">';
								if ( ! empty( $table['caption'] ) ) {
									echo '<caption>' . $table['caption'] . '</caption>';
								}

								if ( ! empty( $table['header'] ) ) {
									echo '<thead>';
										echo '<tr>';
											foreach ( $table['header'] as $th ) {
												echo '<th>';
													echo $th['c'];
												echo '</th>';
											}
										echo '</tr>';
									echo '</thead>';
								}

								echo '<tbody>';
									foreach ( $table['body'] as $tr ) {
										echo '<tr>';
											foreach ( $tr as $td ) {
												echo '<td>';
													echo $td['c'];
												echo '</td>';
											}
										echo '</tr>';
									}
								echo '</tbody>';
							echo '</table>';
						}
						?>
                        </div>
                    </div>
                </div>
            </section>

        <?php endif; ?>

    <?php endwhile; ?>
<?php endif; ?>
