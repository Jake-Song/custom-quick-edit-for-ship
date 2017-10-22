<?php
/*
Plugin Name: Custom Quick Edit
Plugin URI: https://wpjake.com
Description: Extends the quick-edit interface to display additional post meta
Version: 1.0.0
Author: Jake Song
Author URI: http://wpjake.com
Text Domain: custom-quick-edit
*/

class custom_extend_quick_edit{

    private static $instance = null;

    public function __construct(){

        add_action('manage_ship_posts_columns', array($this, 'add_custom_admin_column'), 10, 1); //add custom column
        add_action('manage_posts_custom_column', array($this, 'manage_custom_admin_columns'), 10, 2); //populate column
        add_action('quick_edit_custom_box', array($this, 'display_quick_edit_custom'), 10, 2); //output form elements for quickedit interface
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles')); //enqueue admin script (for prepopulting fields with JS)
        add_action('add_meta_boxes', array($this, 'add_ship_meta_field'), 10, 2); //add metabox to posts to add our meta info
        add_action('save_post', array($this, 'ship_meta_content_save'), 10, 1); //call on save, to update metainfo attached to our metabox
        add_filter('manage_edit-ship_sortable_columns', array($this, 'custom_sortable_columns'));
        add_action('admin_head', array($this, 'custom_postlist_css'));
    }

    //adds a new metabox on our single post edit screen
    public function add_ship_meta_field($post_type, $post) {
      add_meta_box(
        'ship_meta_information',
        __( '판매 선박 정보', 'custom-quick-edit' ),
        array($this, 'ship_meta_information'),
        'ship',
        'normal',
        'low'
      );
      add_meta_box(
        'ship_meta_option',
        __( '선박 옵션', 'custom-quick-edit' ),
        array($this, 'ship_meta_option'),
        'ship',
        'normal',
        'low'
      );
    }

    //metabox output function, displays our fields, prepopulating as needed
    public function ship_meta_information( $post ){
      wp_nonce_field( 'ship_meta', 'ship_meta_field' );
      $content = '';

      $seller = get_post_meta( get_the_ID(), 'seller', true );
      $ship_category = get_post_meta( get_the_ID(), 'ship_category', true );
      $tons = get_post_meta( get_the_ID(), 'tons', true );
      $made_date = get_post_meta( get_the_ID(), 'made_date', true );
      $measure = get_post_meta( get_the_ID(), 'measure', true );
      $certificate = get_post_meta( get_the_ID(), 'certificate', true );
      $sale_location = get_post_meta( get_the_ID(), 'sale_location', true );
      $forward_parts = get_post_meta( get_the_ID(), 'forward_parts', true );
      $made_location = get_post_meta( get_the_ID(), 'made_location', true );
      $commit_name = get_post_meta( get_the_ID(), 'commit_name', true );
      $commit_contact = get_post_meta( get_the_ID(), 'commit_contact', true );

      $content .= "<table class='metabox-input'>";
      $content .= '<tr><td><label for="seller">판매자</label>';

      $content .= "<input type='text' id='seller'
                  name='seller' value='{$seller}' /></td>";

      $content .= "<td><label for='ship_category'>선박유형</label>";
      $content .= "<input type='text' id='ship_category'
                  name='ship_category' value='{$ship_category}' /></td></tr>";

      $content .= '<tr><td><label for="tons">톤수</label>';
      $content .= "<input type='text' id='tons'
                  name='tons' value='{$tons}' /></td>";

      $content .= "<td><label for='made_date'>진수년월일</label>";

      $content .= "<input type='date' id='made_date'
                  name='made_date' value='{$made_date}' /></td></tr>";

      $content .= "<tr><td><label for='measure'>주요 치수</label>";

      $content .= "<input type='text' id='measure'
                  name='measurer' value='{$measure}' /></td>";

      $content .= "<td><label for='certificate'>허가 사항</label>";

      $content .= "<input type='text' id='certificate'
                   name='certificate' value='{$certificate}' /></td></tr>";

      $content .= '<tr><td><label for="sale_location">판매 정박지</label>';

      $content .= "<input type='text' id='sale_location'
                  name='sale_location' value='{$sale_location}' /></td>";

      $content .= '<td><label for="forward_parts">추진 기관</label>';

      $content .= "<input type='text' id='forward_parts'
                  name='forward_parts' value='{$forward_parts}' /></td></tr>";

      $content .= '<tr><td><label for="made_location">조선지</label>';

      $content .= "<input type='text' id='made_location'
                  name='made_location' value='{$made_location}' /></td>";

      $content .= '<td><label for="commit_name">담당자 이름</label>';
      $content .= "<input type='text' id='commit_name'
                  name='commit_name' value='{$commit_name}' /></td></tr>";

      $content .= '<tr><td><label for="commit_contact">연락처</label>';
      $content .= "<input type='text' id='commit_contact'
                  name='commit_contact' value='{$commit_contact}' /></td><td></td></tr>";

      $content .= "</table>";

      echo $content;
    }

    // ship meta option
    public function ship_meta_option( $post ){
      wp_nonce_field( 'ship_meta', 'ship_meta_field' );
      $content = '';

      $ship_option_basic = array(
        'gps' => 'GPS',
        'detecter' => '어군탐지기',
        'rader' => '레이더',
        'ssb' => 'SSB무전기',
        'generator' => '발전기',
        'kitchen' => '주방',
        'toilet' => '화장실'
      );

      $content .= "<table class='metabox-input'>";
      $content .= "<caption>기본 옵션</caption>";

      $current_index = 0;

      foreach ($ship_option_basic as $variable => $name) {

        $$variable = get_post_meta( get_the_ID(), '$variable', true );

        if( $current_index % 2 === 0 ){
          $content .= "<tr><td><label for='{$variable}'>{$name}</label>";

          $content .= "<input type='checkbox' id='{$variable}'
                      name='{$variable}' value='{$$variable}' /></td>";
        } elseif ( $current_index % 2 === 1 ) {
          $content .= "<td><label for='{$variable}'>{$name}</label>";
          $content .= "<input type='checkbox' id='{$variable}'
                      name='{$variable}' value='{$$variable}' /></td></tr>";
        }
        $current_index++;
      }

      $ship_option_addtional = array(
        'aircon' => '에어컨',
        'hitting' => '난방시설',
        'elect_real' => '전동릴공급장치',
        'cctv' => '감시카메라',
        'satelite_phone' => '위성전화기',
        'refridge' => '냉장고',
        'roller' => '해수롤러'
      );
      $content .= "</table>";

      $current_index = 0;
      $content .= "<table class='metabox-input'>";
      $content .= "<caption>부가 옵션</caption>";

      foreach ($ship_option_addtional as $variable => $name) {

        $$variable = get_post_meta( get_the_ID(), '$variable', true );

        if( $current_index % 2 === 0 ){
          $content .= "<tr><td><label for='{$variable}'>{$name}</label>";

          $content .= "<input type='checkbox' id='{$variable}'
                      name='{$variable}' value='{$$variable}' /></td>";
        } elseif ( $current_index % 2 === 1 ) {
          $content .= "<td><label for='{$variable}'>{$name}</label>";
          $content .= "<input type='checkbox' id='{$variable}'
                      name='{$variable}' value='{$$variable}' /></td></tr>";
        }
        $current_index++;
      }

      $content .= "</table>";

      echo $content;
      exit();
      $seller = get_post_meta( get_the_ID(), 'seller', true );
      $ship_category = get_post_meta( get_the_ID(), 'ship_category', true );
      $tons = get_post_meta( get_the_ID(), 'tons', true );
      $made_date = get_post_meta( get_the_ID(), 'made_date', true );
      $measure = get_post_meta( get_the_ID(), 'measure', true );
      $certificate = get_post_meta( get_the_ID(), 'certificate', true );
      $sale_location = get_post_meta( get_the_ID(), 'sale_location', true );
      $forward_parts = get_post_meta( get_the_ID(), 'forward_parts', true );
      $made_location = get_post_meta( get_the_ID(), 'made_location', true );
      $commit_name = get_post_meta( get_the_ID(), 'commit_name', true );
      $commit_contact = get_post_meta( get_the_ID(), 'commit_contact', true );

      $content .= "<table class='metabox-input'>";
      $content .= '<tr><td><label for="seller">판매자</label>';

      $content .= "<input type='text' id='seller'
                  name='seller' value='{$seller}' /></td>";

      $content .= "<td><label for='ship_category'>선박유형</label>";
      $content .= "<input type='text' id='ship_category'
                  name='ship_category' value='{$ship_category}' /></td></tr>";

      $content .= '<tr><td><label for="tons">톤수</label>';
      $content .= "<input type='text' id='tons'
                  name='tons' value='{$tons}' /></td>";

      $content .= "<td><label for='made_date'>진수년월일</label>";

      $content .= "<input type='date' id='made_date'
                  name='made_date' value='{$made_date}' /></td></tr>";

      $content .= "<tr><td><label for='measure'>주요 치수</label>";

      $content .= "<input type='text' id='measure'
                  name='measurer' value='{$measure}' /></td>";

      $content .= "<td><label for='certificate'>허가 사항</label>";

      $content .= "<input type='text' id='certificate'
                   name='certificate' value='{$certificate}' /></td></tr>";

      $content .= '<tr><td><label for="sale_location">판매 정박지</label>';

      $content .= "<input type='text' id='sale_location'
                  name='sale_location' value='{$sale_location}' /></td>";

      $content .= '<td><label for="forward_parts">추진 기관</label>';

      $content .= "<input type='text' id='forward_parts'
                  name='forward_parts' value='{$forward_parts}' /></td></tr>";

      $content .= '<tr><td><label for="made_location">조선지</label>';

      $content .= "<input type='text' id='made_location'
                  name='made_location' value='{$made_location}' /></td>";

      $content .= '<td><label for="commit_name">담당자 이름</label>';
      $content .= "<input type='text' id='commit_name'
                  name='commit_name' value='{$commit_name}' /></td></tr>";

      $content .= '<tr><td><label for="commit_contact">연락처</label>';
      $content .= "<input type='text' id='commit_contact'
                  name='commit_contact' value='{$commit_contact}' /></td><td></td></tr>";

      $content .= "</table>";

      echo $content;
    }

    //enqueue admin js to pre-populate the quick-edit fields
    public function enqueue_admin_scripts_and_styles(){
       wp_enqueue_script('quick-edit-script', plugin_dir_url(__FILE__) .
       '/post-quick-edit-script.js', array('jquery','inline-edit-post' ));
    }
    //Display our custom content on the quick-edit interface, no values can be pre-populated (all done in JS)
    public function display_quick_edit_custom($column){
       $html = '';
       wp_nonce_field('ship_meta', 'ship_meta_field');

       //output post featured checkbox
       if($column == 'product_featured'){
           $html .= '<fieldset class="inline-edit-col-left clear">';
               $html .= '<div class="inline-edit-group wp-clearfix">';
                   $html .= '<label class="alignleft" for="product_featured">';
                      $html .= '<span class="checkbox-title">Product Featured</span></label>';
                      $html .= '<input type="checkbox" name="product_featured" id="product_featured_quick" value="featured"/>';
               $html .= '</div>';
           $html .= '</fieldset>';
       }
   //output post rating select field
   else if($column == 'product_ranking_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
              $html .= "<input type='hidden' id='old_product_ranking_order_quick'
                          name='old_product_ranking_order' value='' />";
              $html .= '<label class="alignleft" for="product_ranking_order">카테고리별 순위</label>';
               $html .= "<input type='checkbox' id='product_ranking_update_quick' name='product_ranking_update' value='updated' />";
               $html .= "<input type='text' id='product_ranking_order_quick'
                           name='product_ranking_order' placeholder='순위를 입력하세요.'
                           value='' />";
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   //output post subtitle text field
   else if($column == 'product_featured_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
               $html .= "<input type='hidden' id='old_product_featured_order_quick'
                           name='old_product_featured_order' value='' />";
               $html .= '<label class="alignleft" for="product_featured_order">Top 30 순위</label>';
               $html .= "<input type='checkbox' id='product_featured_update_quick' name='product_featured_update' value='updated' />";
               $html .= '<input type="text" name="product_featured_order" id="product_featured_order_quick" value="" />';
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   else if($column == 'product_descendant_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
               $html .= "<input type='hidden' id='old_product_descendant_order_quick'
                           name='old_product_descendant_order' value='' />";
               $html .= '<label class="alignleft" for="product_descendant_order">하위 카테고리별 순위</label>';
               $html .= "<input type='checkbox' id='product_descendant_update_quick' name='product_descendant_update' value='updated' />";
               $html .= '<input type="text" name="product_descendant_order" id="product_descendant_order_quick" value="" />';
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   else if($column == 'product_brand_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
               $html .= "<input type='hidden' id='old_product_brand_order_quick'
                           name='old_product_brand_order' value='' />";
               $html .= '<label class="alignleft" for="product_brand_order">브랜드별 순위</label>';
               $html .= "<input type='checkbox' id='product_brand_update_quick' name='product_brand_update' value='updated' />";
               $html .= '<input type="text" name="product_brand_order" id="product_brand_order_quick" value="" />';
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   else if($column == 'product_price'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
               $html .= '<label class="alignleft" for="product_price">가격</label>';
               $html .= '<input type="text" name="product_price" id="product_price_quick" value="" />';
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   echo $html;
    }
    //add a custom column to hold our data
    public function add_custom_admin_column($columns){
      $new_columns = array();

      $new_columns['product_featured'] = 'Top 30';
      $new_columns['product_ranking_order'] = '카테고리별';
      $new_columns['product_featured_order'] = 'Top 30 순위';
      $new_columns['product_descendant_order'] = '하위카테고리';
      $new_columns['product_brand_order'] = '브랜드별';
      $new_columns['product_price'] = '가격';

      return array_merge($columns, $new_columns);
    }
    //customise the data for our custom column, it's here we pull in meatdata info
    public function manage_custom_admin_columns($column_name, $post_id){
      $html = '';

    if($column_name == 'product_featured'){
        $product_featured = get_post_meta($post_id, 'product_featured', true);

        $html .= '<div id="product_featured_' . $post_id . '">';
        if(empty($product_featured)){
            $html .= 'no featured';
        }else if ($product_featured == 'featured'){
            $html .= 'featured';
        }
        $html .= '</div>';
    }
    else if($column_name == 'product_ranking_order'){
        $product_ranking_order = get_post_meta($post_id, 'product_ranking_order', true);

        $html .= '<div id="product_ranking_order_' . $post_id . '">';
            $html .= $product_ranking_order;
        $html .= '</div>';
    }
    else if($column_name == 'product_featured_order'){
        $product_featured_order = get_post_meta($post_id, 'product_featured_order', true);

        $html .= '<div id="product_featured_order_' . $post_id . '">';
            $html .= $product_featured_order;
        $html .= '</div>';
    }
    else if($column_name == 'product_descendant_order'){
        $product_descendant_order = get_post_meta($post_id, 'product_descendant_order', true);

        $html .= '<div id="product_descendant_order_' . $post_id . '">';
            $html .= $product_descendant_order;
        $html .= '</div>';
    }
    else if($column_name == 'product_brand_order'){
        $product_brand_order = get_post_meta($post_id, 'product_brand_order', true);

        $html .= '<div id="product_brand_order_' . $post_id . '">';
            $html .= $product_brand_order;
        $html .= '</div>';
    }
    else if($column_name == 'product_price'){
        $product_price = get_post_meta($post_id, 'product_price', true);

        $html .= '<div id="product_price_' . $post_id . '">';
            $html .= $product_price;
        $html .= '</div>';
    }
    echo $html;
    }
    
    //saving meta info (used for both traditional and quick-edit saves)
    public function ship_meta_content_save($post_id){
      $post_type = get_post_type( $post_id );

      if( $post_type === 'ship' ) :

        if( !isset( $_POST['post_author'] ) ) return;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

        if ( !wp_verify_nonce( $_POST['ship_meta_field'], 'ship_meta') )
        return;

        if ( 'page' == $_POST['post_type'] ) {
          if ( !current_user_can( 'edit_page', $post_id ) )
          return;
        } else {
          if ( !current_user_can( 'edit_post', $post_id ) )
          return;
        }

        $product_ranking_order = isset($_POST['product_ranking_order']) ?
        sanitize_text_field($_POST['product_ranking_order']) : 0;

        $product_descendant_order = isset($_POST['product_descendant_order']) ?
        sanitize_text_field($_POST['product_descendant_order']) : 0;

        $product_brand_order = isset($_POST['product_brand_order']) ?
        sanitize_text_field($_POST['product_brand_order']) : 0;

        $product_featured = isset($_POST['product_featured']) ?
        sanitize_text_field($_POST['product_featured']) : '';

        $product_featured_order = isset($_POST['product_featured_order']) ?
        sanitize_text_field($_POST['product_featured_order']) : 0;

        $product_price = isset($_POST['product_price']) ?
        sanitize_text_field($_POST['product_price']) : '';

        $checkout_url = isset($_POST['checkout_url']) ?
        esc_url( $_POST['checkout_url']) : '';

        $old_product_ranking_order = isset($_POST['old_product_ranking_order']) ?
        sanitize_text_field($_POST['old_product_ranking_order']) : 0;

        $old_product_featured_order = isset($_POST['old_product_featured_order']) ?
        sanitize_text_field($_POST['old_product_featured_order']) : 0;

        $old_product_descendant_order = isset($_POST['old_product_descendant_order']) ?
        sanitize_text_field($_POST['old_product_descendant_order']) : 0;

        $old_product_brand_order = isset($_POST['old_product_brand_order']) ?
        sanitize_text_field($_POST['old_product_brand_order']) : 0;

        $product_ranking_changed = !empty($old_product_ranking_order) ?
          $old_product_ranking_order - $product_ranking_order : 0;

        $featured_ranking_changed = !empty($old_product_featured_order) ?
          $old_product_featured_order - $product_featured_order : 0;

        $descendant_ranking_changed = !empty($old_product_descendant_order) ?
          $old_product_descendant_order - $product_descendant_order : 0;

        $brand_ranking_changed = !empty($old_product_brand_order) ?
          $old_product_brand_order - $product_brand_order : 0;

        $product_ranking_update = isset($_POST['product_ranking_update']) ?
            sanitize_text_field($_POST['product_ranking_update']) : "";
        $product_descendant_update = isset($_POST['product_descendant_update']) ?
          sanitize_text_field($_POST['product_descendant_update']) : "";
        $product_brand_update = isset($_POST['product_brand_update']) ?
            sanitize_text_field($_POST['product_brand_update']) : "";
        $product_featured_update = isset($_POST['product_featured_update']) ?
            sanitize_text_field($_POST['product_featured_update']) : "";

        if( $product_ranking_update === 'updated' ){

          $ranking_update_date = date( 'Y-m-d h:i:s' );
          update_post_meta( $post_id, 'ranking_update_date', $ranking_update_date );

          update_post_meta( $post_id, 'product_ranking_order', $product_ranking_order );
          update_post_meta( $post_id, 'product_ranking_changed', $product_ranking_changed );
        }

        if( $product_descendant_update === 'updated' ){

          $descendant_update_date = date( 'Y-m-d h:i:s' );
          update_post_meta( $post_id, 'descendant_update_date', $descendant_update_date );

          update_post_meta( $post_id, 'product_descendant_order', $product_descendant_order );
          update_post_meta( $post_id, 'descendant_ranking_changed', $descendant_ranking_changed );
        }

        if( $product_brand_update === 'updated' ){

          $brand_update_date = date( 'Y-m-d h:i:s' );
          update_post_meta( $post_id, 'brand_update_date', $brand_update_date );

          update_post_meta( $post_id, 'product_brand_order', $product_brand_order );
          update_post_meta( $post_id, 'brand_ranking_changed', $brand_ranking_changed );
        }

        if( $product_featured_update === 'updated' ){

          $featured_update_date = date( 'Y-m-d h:i:s' );
          $test = 0;
          update_post_meta( $post_id, 'featured_update_date', $featured_update_date );

          update_post_meta( $post_id, 'product_featured_order', $product_featured_order );
          update_post_meta( $post_id, 'featured_ranking_changed', $featured_ranking_changed );
        }

        update_post_meta( $post_id, 'product_featured', $product_featured );
        update_post_meta( $post_id, 'product_price', $product_price );
        update_post_meta( $post_id, 'checkout_url', $checkout_url );

      endif;
    }

    // add a sortable filter
    public function custom_sortable_columns($sortable_columns){
      $sortable_columns[ 'product_ranking_order' ] = 'product_ranking_order';
      $sortable_columns[ 'product_featured_order' ] = 'product_featured_order';
      $sortable_columns[ 'product_featured' ] = 'product_featured';
      $sortable_columns['product_descendant_order'] = 'product_descendant_order';
      $sortable_columns['product_brand_order'] = 'product_brand_order';

      return $sortable_columns;
    }

    // admin post list css
    public function custom_postlist_css() {

      $post_type = get_post_type();
      if ($post_type == 'ship') :
      echo '
        <style>
          th#title{
            width: 25%;
          }
          th#taxonomy-ship_category, th#taxonomy-ship_brand{
            width: 10%;
          }
          th#product_featured{
            width: 7%;
          }
          th#product_ranking_order,
          th#product_featured_order,
          th#product_descendant_order
          th#product_brand_order{
            width: 6%;
          }
          td.product_ranking_order,
          td.product_featured_order,
          td.product_descendant_order,
          td.product_brand_order{
            text-align: center;
          }
        </style>
      ';
      endif;
    }

    // gets singleton instance
    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }


}
$custom_extend_quick_edit = custom_extend_quick_edit::getInstance();
