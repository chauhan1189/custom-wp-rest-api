<?php 
function product_data( $posts ){
  $data = [];
	$i = 0;
	foreach ( $posts as $key => $post ) {
		$data[$i]['id'] = $post->ID;
		$data[$i]['title'] = $post->post_title;
		$data[$i]['content'] = $post->post_content;
		$data[$i]['slug'] = $post->post_name;
		$data[$i]["post_author"] = $post->post_author;
		$data[$i]['post_date'] = $post->post_date;
		$data[$i]['post_excerpt'] = $post->post_excerpt;
		$data[$i]['post_status'] = $post->post_status;
		$data[$i]['post_type'] = $post->post_type;
		$data[$i]['featured_image']['thumbnail'] = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
		$data[$i]['featured_image']['medium'] = get_the_post_thumbnail_url( $post->ID, 'medium' );
		$data[$i]['featured_image']['large'] = get_the_post_thumbnail_url( $post->ID, 'large' );
		$data[$i]['featured_image']['full'] = get_the_post_thumbnail_url( $post->ID, 'full' );
		$i++;
	}
	return $data;
}

/*-------------------------------------------*/
/*-------------Get Related posts-------------*/
/*-------------------------------------------*/
function related_products_endpoint( $request_data ) {

    $product_id = $request_data['product_id'];

    $posts = get_posts(
        array(
            'post_type' => 'product',
            'category__in'   => wp_get_post_categories($product_id),
            'posts_per_page' => POSTS_PER_PAGE,
            'post__not_in'   => array($product_id),
        )
    );
    $data = product_data( $posts );
    return  $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/products/related/(?P<product_id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => 'related_products_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-------------------------------------------*/
/*-------------Get Recent posts-------------*/
/*-------------------------------------------*/
function recent_products_endpoint() {
    $posts = get_posts(
        array(
            'post_type' => 'product',
            'posts_per_page' => POSTS_PER_PAGE,
        )
    );
    $data = product_data( $posts );
    return  $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/products/recent', array(
            'methods' => 'GET',
            'callback' => 'recent_products_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-----------------------------------------------*/
/*-------------Get posts by category-------------*/
/*-----------------------------------------------*/

function category_products_endpoint( $request_data ) {

    $cat_id = $request_data['cat_id'];
    $posts = get_posts(
        array( 
          'post_type' => 'product', 
          'posts_per_page' => POSTS_PER_PAGE,
          'post_status' => 'publish', 
          'tax_query' => array( 
            array(
              'taxonomy' => 'product_cat',
              'field'    => 'term_id',
              'terms'     =>  [$cat_id],
              'operator'  => 'IN'
            )
          )
        )
    );
    $data = product_data( $posts );
    return  $data;
}
add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/category/products/(?P<cat_id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => 'category_products_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-----------------------------------------------*/
/*---------------Get posts by tag----------------*/
/*-----------------------------------------------*/

function tags_products_endpoint( $request_data ) {

    $tag_id = $request_data['tag_id'];
    $posts = get_posts(
        array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => POSTS_PER_PAGE,
            'tax_query' => array(
              array(
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => $tag_id,
                'operator' => 'IN',
             )
          )
        )
    );
    if ( empty( $posts ) ) {
	    return new WP_Error( 'no_tag', 'Invalid tag ', array( 'status' => 404 ) );
	}
	$data = product_data( $posts );
    return  $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/tags/products/(?P<tag_id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => 'tags_products_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-----------------------------------------------------*/
/*--------------Get posts by the author----------------*/
/*-----------------------------------------------------*/

function author_products_endpoint( $request_data ) {
    $posts = get_posts( array(
      'author' => $request_data['id'],
    ) );
 
    if ( empty( $posts ) ) {
      return new WP_Error( 'no_author', 'Invalid author', array( 'status' => 404 ) );
    }
    $data = product_data( $posts );
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/author/products/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'author_products_endpoint',
        'permission_callback' => '__return_true',
      ) );
    } 
);

/*--------------------------------------------------------------*/
/*--------------Get next post and previous posts----------------*/
/*--------------------------------------------------------------*/

function featured_products_endpoint( $request_data ) {

  	$posts = get_posts(
          array(
              'post_type' => 'product',
              'posts_per_page' => POSTS_PER_PAGE,
              'meta_key'   => '_is_ns_featured_post',
    			'meta_value' => 'yes',
          )
      );
    
   
    if ( empty( $posts ) ) {
      return new WP_Error( 'no_post', 'no such post', array( 'status' => 404 ) );
    }
    $data = product_data( $posts );
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/products/featured/', array(
      'methods' => 'GET',
      'callback' => 'featured_products_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );

/*--------------------------------------------------------------*/
/*------------------Get Single posts details--------------------*/
/*--------------------------------------------------------------*/

function single_product_endpoint( $request_data ) {

    global $wpdb;
    $sql = 'SELECT * from '.$wpdb->prefix.'posts where ID = '.$request_data['product_id'];
    $posts = $wpdb->get_results( $sql );
   
    if ( empty( $posts ) ) {
      return new WP_Error( 'no_product', 'no such product', array( 'status' => 404 ) );
    }
    $data = product_data( $posts );
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/product/single/(?P<product_id>\d+)', array(
      'methods' => 'GET',
      'callback' => 'single_product_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );


/*---------------------------------------------------------------------------*/
/*------------------Get next and previous Single products--------------------*/
/*---------------------------------------------------------------------------*/

function adjacent_product_endpoint( $request_data ) {

    $data = [];
    global $wpdb;
    $sql = 'SELECT * from '.$wpdb->prefix.'posts where ( ID > '.$request_data['product_id'].' and post_type = "product" and post_status = "publish") limit 1';
    $next = $wpdb->get_row( $sql );
    $sql = 'SELECT * from '.$wpdb->prefix.'posts where ( ID < '.$request_data['product_id'].' and post_type = "product" and post_status = "publish") limit 1';
    $prev = $wpdb->get_row( $sql );
    $post_next['next'] = $next;
    if(!empty( $next )){
      $data['next'] = product_data( $post_next );
    }else{
      $data['next']= [];
    }
    $post_prev['prev'] = $prev;
    if(!empty( $prev )){
      $data['prev'] = product_data( $post_prev );
    }else{
      $data['prev']= [];
    }
    if ( empty( $data ) ) {
      return new WP_Error( 'no_product', 'no such product', array( 'status' => 404 ) );
    }
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/product/adjacent/(?P<product_id>\d+)', array(
      'methods' => 'GET',
      'callback' => 'adjacent_product_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );