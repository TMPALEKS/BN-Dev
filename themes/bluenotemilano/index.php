<?php get_header(); ?>


<div class="page-wrap">

    <div class="left sizeMedium">

        <div id="carousel" class="carousel slide">
            <!-- Carousel items -->
            <?php 
              	if ( strtolower(ICL_LANGUAGE_NAME) == 'italiano') $catname = 'in-evidenza';
            	else if ( strtolower(ICL_LANGUAGE_NAME) == 'english') $catname = 'headline-news';

            ?>
            <div class="carousel-inner">
                <?php
                $args  = array(
                    'numberposts'   => BNMExtendsOptions::numberFeatured(),
                    'category_name' => $catname
                );
                $posts = get_posts( $args );
                $index = 0;
                foreach ( $posts as $post ) : $active = ($index == 0) ? 'active' : '' ?>
                    <?php $navigation .= '<span data-index="' . $index .'" class="'.$active.'"></span>' ?>
                    <div class="item <?php echo $active ?>" data-index="<?php echo $index ?>">
                        <a href="<?php echo get_post_permalink( $post->ID ) ?>">
                            <img
                                alt="<?php echo get_the_title( $post->ID ) ?>"
                                title="<?php echo get_the_title( $post->ID ) ?>"
                                src="<?php
                                    $image_id = get_post_thumbnail_id( $post->ID );
                                    $image    = wp_get_attachment_image_src( $image_id, 'full' );
                                    echo $image[0] ?>"/>
                        </a>
                        <div class="carousel-caption">
                            <a href="<?php echo get_post_permalink( $post->ID ) ?>"><?php echo get_the_title( $post->ID ) ?></a>
                        </div>
                    </div>
                    <?php $index++; endforeach; ?>
            </div>

            <!-- Carousel nav -->
            <a class="carousel-control left" href="#carousel" data-slide="prev">&lsaquo;</a>
            <a class="carousel-control right" href="#carousel" data-slide="next">&rsaquo;</a>

        </div>

        <div class="carousel-navigation clearfix">
            <?php echo $navigation ?>
        </div>

        <div class="content box blue">
            <?php BNMExtendsCalendar::calendarCompact(); ?>
            <br style="clear: both"/>
        </div>

    </div>

    <?php get_sidebar( 'home' ) ?>

</div>

<?php get_footer();