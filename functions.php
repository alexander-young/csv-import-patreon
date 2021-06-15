<?php

// so we can test out the thumbnails
add_theme_support( 'post-thumbnails' );

$csv = get_stylesheet_directory() . '/posts.csv';

if(file_exists( $csv ) && is_admin() && $_GET['csv'] == 1){
    
    $handle = fopen($csv, "r");

    $i = 0;
    while (($data = fgetcsv($handle)) !== false) {
        if($i > 0){ // skip header
            
            if(!empty($data[3])){

                $cat_slugs = explode(',', $data[3]);

                $cat_ids = [];
                
                if($cat_slugs){
                
                    foreach($cat_slugs as $slug){
                
                        $term_obj = get_category_by_slug( $slug );
                
                
                        if($term_obj !== false){
                
                            $cat_ids[] = $term_obj->term_id;
                
                        }else{ // category doesn't exist, let's make it
                
                            $cat_id = wp_create_category( $slug );
                
                            if(!is_wp_error( $cat_id )){
                                $cat_ids[] = $cat_id;
                            }
                
                        }
                
                    }
                
                }
                
            }

            $post_id = wp_insert_post( [
                'post_title' => $data[0],
                'post_content' => $data[1],
                'post_category' => $cat_ids,
                'post_status' => 'publish'
            ] );

            if(!is_wp_error( $post_id )){

                if(!empty($data[2])){
                
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    
                    $attachment_id = media_sideload_image( $data[2], $post_id, '', 'id' );
                
                    if(!is_wp_error( $attachment_id )){
                
                        set_post_thumbnail( $post_id, $attachment_id );
                
                    }
                    
                }

            }


        }
        $i++;
    }

}
