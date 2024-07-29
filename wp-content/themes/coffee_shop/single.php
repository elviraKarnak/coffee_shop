<?php

get_header(); 

    if(have_posts()) : while (have_posts() ) : the_post(); 
?>


<section class="section_wrap p-t p-b">
    <h2 class="page-title"><?php the_title(); ?></h2>
    <div class="container">
          <div class="row">
            <div class="col-md-12">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
</section>


<?php endwhile; endif; 

get_footer(); ?>