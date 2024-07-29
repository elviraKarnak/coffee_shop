<?php 
/**
* Template Name: PAGE::Home Page 
**/
get_header(); ?>

<section class="main_banner">
        <div class="container-fluid">
            <div class="main_banner-inner" style="background-image: url(<?php echo get_template_directory_uri(); ?>/asset/images/banner.jpg);">
                <div class="row justify-content-center align-items-center">
                    <div class="col-xl-6 col-xxl-4">
                        <div class="banner-content text-center">
                            <h1>A Greek Odyssey in Groningen</h1>
                            <p>Coffee, Pastries & More Await</p>
                            <div class="hero_button-group">
                                <a href="#" class="btnPrime btnbg">Order Now</a>
                                <a href="#" class="btnPrime btnoutline">Menu</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sectionSpacing-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="three-colmn-wrap">
                        <div class="title_wrap text-center">
                            <h2>Why You'll love Argo</h2>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="home_card-colmn text-center">
                                    <figure class="img_thumb">
                                        <img src="<?php echo get_template_directory_uri(); ?>/asset/images/1.png" alt="">
                                    </figure>
                                    <h4>Freshly ground delights</h4>
                                    <p>Savour our signature Freddo coffees and explore our menu for more</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="home_card-colmn text-center">
                                    <figure class="img_thumb">
                                        <img src="<?php echo get_template_directory_uri(); ?>/asset/images/3.png" alt="">
                                    </figure>
                                    <h4>A Taste of Home: Shop Greek Groceries:</h4>
                                    <p>Stock your pantry with essential Greek ingredients and discover unique products
                                        from producers we know</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="home_card-colmn text-center">
                                    <figure class="img_thumb">
                                        <img src="<?php echo get_template_directory_uri(); ?>/asset/images/2.png" alt="">
                                    </figure>
                                    <h4>Sweet creations</h4>
                                    <p> Savour our fresh, authentic Greek pastries, like baklava and galaktoboureko.
                                        Explore more on our menu!</p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </section>

    <section class="sectionSpacing-2 bg_graphics-home">
        <div class="container">
            <div class="graphics_bg-wrap">
                <div class="top_land-content">
                    <h3>A delicious destiny</h3>
                    <p>Argo, named after the legendary ship of discovery. Mikel's infectious enthusiasm curates a selection of the finest Greek groceries, offering a taste of Greece in every aisle. And, Themos, the dough whisperer, fills Argo with the irresistible aroma of freshly baked pastries and desserts.</p>
                    <a href="#" class="btnlarge">read more</a>
                </div>
                <div class="center_img-wrap"><img src="<?php echo get_template_directory_uri(); ?>/asset/images/human_graphics.png" alt=""></div>

                <div class="bottom_land-content">
                    <h4>Together, they've created... </h4>
                    <p>A haven unlike any other – a cosy corner of Greece in the heart of Groningen. Come experience the warmth of Greek culture, the satisfying aroma of freshly brewed coffee, and the delightful display of authentic treats</p>
                    <a href="#" class="btnlarge">read more</a>
                </div>
            </div>
        </div>
    </section>

    <section class="sectionSpacing-5 show_brand-wrap">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-5 order-xl-1 order-2">
                    <div class="left_content-block">
                        <figure><img src="<?php echo get_template_directory_uri(); ?>/asset/images/Grab&Go_Logo_V2 1.svg" alt=""></figure>
                        <h2>Skip the line - <span>enjoy</span> your time!</h2>
                        <p>Order your favorite treats, select your pickup time, pay online, and grab your order without waiting in line. Enjoy a hassle-free experience with our Grab & Go service</p>
                        <a href="#" class="btnlarge">Place an order</a>
                    </div>
                 </div>
                 <div class="col-md-7 d-flex justify-content-end order-xl-2 order-1">
                    <div class="block_brand-display">
                        <div class="circle_object">
                            <div class="object_one">
                                <img src="<?php echo get_template_directory_uri(); ?>/asset/images/cup.png" alt="">
                              </div>
                              <div class="object_two">
                                <img src="<?php echo get_template_directory_uri(); ?>/asset/images/papper-bag.png" alt="">
                              </div>
                        </div>
                      
                    </div>
                 </div>
            </div>
             
        </div>
    </section>

<?php get_footer(); ?>