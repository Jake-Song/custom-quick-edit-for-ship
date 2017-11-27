<?php
/*
Plugin Name: Custom Meta Box
Plugin URI: https://wpjake.com
Description: Extends the meta box to display additional post meta
Version: 1.0.0
Author: Jake Song
Author URI: http://wpjake.com
Text Domain: custom-quick-edit
*/

class custom_meta_box{

    private static $instance = null;

    private static $ship_information = array(
      'seller' => '판매자',
      'ship_category' => '선박유형',
      'tons' => '톤수',
      'made_date' => '진수년월일',
      'measure' => '주요치수',
      'certificate' => '허가사항',
      'sale_location' => '판매지역',
      'forward_parts' => '추진기관',
      'made_location' => '조선지',
      'price' => '매매가',
      'commit_name' => '담당자',
      'commit_contact' => '연락처',
    );

    private static $ship_option_basic = array(
      'gps' => 'GPS',
      'detecter' => '어군탐지기',
      'rader' => '레이더',
      'ssb' => 'SSB무전기',
      'generator' => '발전기',
      'kitchen' => '주방',
      'toilet' => '화장실'
    );

    private static $ship_option_addtional = array(
      'aircon' => '에어컨',
      'hitting' => '난방시설',
      'elect_real' => '전동릴공급장치',
      'cctv' => '감시카메라',
      'satelite_phone' => '위성전화기',
      'refridge' => '냉장고',
      'roller' => '해수롤러'
    );

    public function __construct(){

        add_action('add_meta_boxes', array($this, 'add_ship_meta_field'), 10, 2); //add metabox to posts to add our meta info
        add_action('save_post', array($this, 'ship_meta_content_save'), 10, 1); //call on save, to update metainfo attached to our metabox
        add_action('admin_head', array($this, 'custom_postlist_css'));
    }

    //adds a new metabox on our single post edit screen
    public function add_ship_meta_field($post_type, $post) {
      add_meta_box(
        'ship_meta_featured',
        __( '베스트 선박', 'custom-quick-edit' ),
        array($this, 'ship_meta_featured'),
        'ship',
        'normal',
        'low'
      );
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
      $content .= "<table class='metabox-input'>";
      $current_index = 0;

      foreach ( self::$ship_information as $key => $value ) {
        $$key = get_post_meta( get_the_ID(), $key, true );

        if($current_index % 2 === 0){
          $content .= "<tr><td><label for='{$key}'>{$value}</label>";

          $content .= "<input type='text' id='{$key}'
                      name='{$key}' value='{$$key}' /></td>";
        } elseif ($current_index % 2 === 1) {
          $content .= "<td><label for='{$key}'>{$value}</label>";
          $content .= "<input type='text' id='{$key}'
                      name='{$key}' value='{$$key}' /></td></tr>";
        }

        $current_index++;

      }

      $content .= "</table>";

      echo $content;
    }

    // ship meta option
    public function ship_meta_option( $post ){
      wp_nonce_field( 'ship_meta', 'ship_meta_field' );

      $content = '';
      $content .= "<table class='metabox-input'>";
      $content .= "<caption>기본 옵션</caption>";

      $current_index = 0;

      foreach (self::$ship_option_basic as $key => $value) {

        $$key = get_post_meta( get_the_ID(), $key, true );
        $key_attr = !empty($$key) ? ' checked' : "";

        if( $current_index % 2 === 0 ){
          $content .= "<tr><td><label for='{$key}'>{$value}</label>";

          $content .= "<input type='checkbox' id='{$key}'
                      name='{$key}' value='{$key}' {$key_attr} /></td>";
        } elseif ( $current_index % 2 === 1 ) {
          $content .= "<td><label for='{$key}'>{$value}</label>";
          $content .= "<input type='checkbox' id='{$key}'
                      name='{$key}' value='{$key}' {$key_attr} /></td></tr>";
        }
        $current_index++;
      }

      $content .= "</table>";

      $current_index = 0;
      $content .= "<table class='metabox-input'>";
      $content .= "<caption>부가 옵션</caption>";

      foreach (self::$ship_option_addtional as $key => $value) {

        $$key = get_post_meta( get_the_ID(), $key, true );
        $$key_attr = !empty($$key) ? ' checked' : "";

        if( $current_index % 2 === 0 ){
          $content .= "<tr><td><label for='{$key}'>{$value}</label>";

          $content .= "<input type='checkbox' id='{$key}'
                      name='{$key}' value='{$key}' {$$key_attr} /></td>";
        } elseif ( $current_index % 2 === 1 ) {
          $content .= "<td><label for='{$key}'>{$value}</label>";
          $content .= "<input type='checkbox' id='{$key}'
                      name='{$key}' value='{$key}' {$$key_attr} /></td></tr>";
        }
        $current_index++;
      }

      $content .= "</table>";

      echo $content;

    }

    // Ship Meta Featured
    public function ship_meta_featured( $post ){
      wp_nonce_field( 'ship_meta', 'ship_meta_field' );

      $best_featured = get_post_meta( get_the_ID(), 'best_featured', true );
      $best_featured_attr = !empty($best_featured) ? ' checked' : "";

      $content = '';
      $content = "베스트 선박으로 추가하려면 선택해주세요. <input type='checkbox' name='best_featured' {$best_featured_attr} value='best_featured' />";
      echo $content;
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

        $best_featured = isset($_POST['best_featured']) ? sanitize_text_field($_POST['best_featured']) : "";
        update_post_meta( $post_id, 'best_featured', $best_featured );

        foreach (self::$ship_information as $key => $value) {
          $$key = isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : "";
          update_post_meta( $post_id, $key, $$key );
        }

        $ship_option = array_merge(self::$ship_option_basic, self::$ship_option_addtional);

        foreach ($ship_option as $variable => $value) {
          $$variable = isset($_POST[$variable]) ? sanitize_text_field($_POST[$variable]) : "";
          update_post_meta( $post_id, $variable, $$variable );
        }

      endif;
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
$custom_meta_box = custom_meta_box::getInstance();
