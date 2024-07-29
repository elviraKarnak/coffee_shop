<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <!-- Required meta tags -->
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1, initial-scale=1, shrink-to-fit=no">
  <title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; <?php } ?> <?php wp_title(); ?></title>
  <!-- style sheets -->
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php  wp_body_open();?>

<header class="site_header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-2 d-flex align-items-center">
                <div class="site_logo">
                    <?php if(has_custom_logo()):
                        the_custom_logo();
                    endif;  
                    ?>
                        
                </div>
                <div class="btnToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <div class="col-xl-10">
                <div class="mobile_menu_wrap">
                    <div class="row">
                        <div class="col-xl-9 d-xl-flex justify-content-xl-center">
                        <?php
                                wp_nav_menu(
                                    array(
                                    'container'            => 'div',
                                    'container_class'      => 'main_menunav',
                                    'container_id'         => '',
                                    'items_wrap'     => '<ul id="%1$s" class="%2$s ">%3$s</ul>',
                                    'theme_location' => 'menu-1',
                                    )
                                );
                            ?> 
                        </div>
                        <div class="col-xl-3 d-xl-flex justify-content-xl-end">
                            <div class="header_action">
                                <div class="select-lang-wrap">
                                 <select class="select_lang">
                                    <option> EN </option>
                                    <option> NIL </option>
                                 </select>
                                </div>
                                <?php
                                wp_nav_menu(
                                    array(
                                    'container'            => 'div',
                                    'container_class'      => 'btn_panel',
                                    'container_id'         => '',
                                    'items_wrap'     => '<ul id="%1$s" class="%2$s ">%3$s</ul>',
                                    'theme_location' => 'menu-2',
                                    )
                                );
                            ?> 
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
           
        </div>
    </div>
</header>