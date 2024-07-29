<?php

get_header(); 

    if(have_posts()) : while (have_posts() ) : the_post(); 
?>


<section class="section_wrap m-5">
    
<div class="container m-auto">
    <h2 class="page-title mt-5 pb-5"><?php the_title(); ?></h2>
          <div class="row">
            <div class="col-md-12">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
</section>


<?php endwhile; endif; 

get_footer(); ?>