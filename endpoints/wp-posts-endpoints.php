<?php 
function post_data( $posts ){
  $data = [];
	$i = 0;
	foreach ( $posts as $key => $post ) {
		$data[$i]['id'] = $post->ID;
		$data[$i]['title'] = $post->post_content;
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
function related_posts_endpoint( $request_data ) {

    $post_id = $request_data['post_id'];

    $posts = get_posts(
        array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'category__in'   => wp_get_post_categories($post_id),
            'posts_per_page' => POSTS_PER_PAGE,
            'post__not_in'   => array($post_id),
        )
    );
    $data = post_data( $posts );
    return  $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/posts/related/(?P<post_id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => 'related_posts_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-------------------------------------------*/
/*-------------Get Recent posts-------------*/
/*-------------------------------------------*/
function recent_posts_endpoint() {
    $posts = get_posts(
        array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => POSTS_PER_PAGE,
        )
    );
    $data = post_data( $posts );
    return  $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/posts/recent', array(
            'methods' => 'GET',
            'callback' => 'recent_posts_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-----------------------------------------------*/
/*-------------Get posts by category-------------*/
/*-----------------------------------------------*/

function category_posts_endpoint( $request_data ) {
    $cat_id = $request_data['cat_id'];
    $posts = get_posts(
        array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'category__in'  => array( $cat_id ),
            'posts_per_page' => POSTS_PER_PAGE,
        )
    );
    $data = post_data( $posts );
    return  $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/category/posts/(?P<cat_id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => 'category_posts_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-----------------------------------------------*/
/*---------------Get posts by tag----------------*/
/*-----------------------------------------------*/

function tags_posts_endpoint( $request_data ) {

    $tag_id = $request_data['tag_id'];
    $posts = get_posts(
        array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => POSTS_PER_PAGE,
            'tax_query' => array(
              array(
                  'taxonomy' => 'post_tag',
                  'field' => 'term_id',
                  'terms' => $tag_id,
              )
          )
        )
    );

    if ( empty( $posts ) ) {
	    return new WP_Error( 'no_tag', 'Invalid tag ', array( 'status' => 404 ) );
	}
	$data = post_data( $posts );
    return  $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/tags/posts/(?P<tag_id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => 'tags_posts_endpoint',
            'permission_callback' => '__return_true',
    ));

});

/*-----------------------------------------------------*/
/*--------------Get posts by the author----------------*/
/*-----------------------------------------------------*/

function author_posts_endpoint( $request_data ) {
  $posts = get_posts( array(
    'author' => $request_data['id'],
    'post_status' => 'publish',
  ) );
 
  if ( empty( $posts ) ) {
    return new WP_Error( 'no_author', 'Invalid author', array( 'status' => 404 ) );
  }
  $data = post_data( $posts );
  return $data;
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v1', '/author/posts/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'author_posts_endpoint',
    'permission_callback' => '__return_true',
  ) );
} );

/*--------------------------------------------------------------*/
/*--------------Get next post and previous posts----------------*/
/*--------------------------------------------------------------*/

function featured_posts_endpoint( $request_data ) {

	$posts = get_posts(
        array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => POSTS_PER_PAGE,
            'meta_key'   => '_is_ns_featured_post',
  			'meta_value' => 'yes',
        )
    );
  
 
  if ( empty( $posts ) ) {
    return new WP_Error( 'no_post', 'no such post', array( 'status' => 404 ) );
  }
  $data = post_data( $posts );
  return $data;
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp/v1', '/posts/featured/', array(
    'methods' => 'GET',
    'callback' => 'featured_posts_endpoint',
    'permission_callback' => '__return_true',
  ) );
} );

/*--------------------------------------------------------------*/
/*------------------Get Single posts details--------------------*/
/*--------------------------------------------------------------*/

function single_post_endpoint( $request_data ) {

    global $wpdb;
    $sql = 'SELECT * from '.$wpdb->prefix.'posts where ID = '.$request_data['post_id'];
    $posts = $wpdb->get_results( $sql );
  	//$posts = get_post( $request_data['post_id'] );
    if ( empty( $posts ) ) {
      return new WP_Error( 'no_post', 'no such post', array( 'status' => 404 ) );
    }
    $data = post_data( $posts );
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/post/single/(?P<post_id>\d+)', array(
      'methods' => 'GET',
      'callback' => 'single_post_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );


/*------------------------------------------------------------*/
/*------------------Get all the categories--------------------*/
/*------------------------------------------------------------*/

function categories_endpoint() {

    $categories = get_categories();
    $data = [];
    foreach($categories as $category) {
        $category->category_link = get_category_link( $category->term_id );
        array_push( $data, $category);
    }
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/posts/categories', array(
      'methods' => 'GET',
      'callback' => 'categories_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );


/*------------------------------------------------------*/
/*------------------Get all the tags--------------------*/
/*------------------------------------------------------*/

function tags_endpoint() {
    $tags = get_tags();
    $data = [];
    foreach ( $tags as $tag ) {
        $tag_link = get_tag_link( $tag->term_id );
        $tag->tag_link = $tag_link;
        array_push( $data, $tag );
    }
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/posts/tags', array(
      'methods' => 'GET',
      'callback' => 'tags_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );


/*---------------------------------------------------------------------------*/
/*------------------Get next and previous Single posts--------------------*/
/*---------------------------------------------------------------------------*/

function adjacent_post_endpoint( $request_data ) {

    $data = [];
    global $wpdb;
    $sql = 'SELECT * from '.$wpdb->prefix.'posts where ( ID > '.$request_data['post_id'].' and post_type = "post" and post_status = "publish") limit 1';
    $next = $wpdb->get_row( $sql );
    $sql = 'SELECT * from '.$wpdb->prefix.'posts where ( ID < '.$request_data['post_id'].' and post_type = "post" and post_status = "publish") limit 1';
    $prev = $wpdb->get_row( $sql );
    $post_next['next'] = $next;
    if(!empty( $next )){
      $data['next'] = post_data( $post_next );
    }else{
      $data['next']= [];
    }
    $post_prev['prev'] = $prev;
    if(!empty( $prev )){
      $data['prev'] = post_data( $post_prev );
    }else{
      $data['prev']= [];
    }
    if ( empty( $data ) ) {
      return new WP_Error( 'no_post', 'no such post', array( 'status' => 404 ) );
    }
    return $data;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/post/adjacent/(?P<post_id>\d+)', array(
      'methods' => 'GET',
      'callback' => 'adjacent_post_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );
/*--------------------------------------------------------------------*/
/*------------------Get all the comments of a post--------------------*/
/*--------------------------------------------------------------------*/

function comments_endpoint( $request_data ) {
    $comments = get_comments(array(
        'post_id' => $request_data['post_id'],
        'status' => 'approve' //Change this to the type of comments to be displayed
    ));
    return $comments;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', '/comments/(?P<post_id>\d+)', array(
      'methods' => 'GET',
      'callback' => 'comments_endpoint',
      'permission_callback' => '__return_true',
    ) );
} );