<?php
if (!defined('ABSPATH')) { exit; }

require_once get_theme_file_path('/inc/client-profile.php');
require_once get_theme_file_path('/inc/creditos.php');

function legacyx_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
    register_nav_menus(array('primary' => __('Primary Navigation','legacy-x-firm')));
}
add_action('after_setup_theme','legacyx_theme_setup');

function legacyx_enqueue_assets() {
    $css = get_stylesheet_directory() . '/style.css';
    $polish = get_stylesheet_directory() . '/assets/css/polish.css';
    $assessment = get_stylesheet_directory() . '/assets/css/assessment.css';
    wp_enqueue_style('legacyx-style', get_stylesheet_uri(), array(), file_exists($css) ? filemtime($css) : '1.0.0');
    wp_enqueue_style('legacyx-polish', get_stylesheet_directory_uri() . '/assets/css/polish.css', array('legacyx-style'), file_exists($polish) ? filemtime($polish) : '1.0.0');
    if (is_page('executive-assessment')) wp_enqueue_style('legacyx-assessment', get_stylesheet_directory_uri() . '/assets/css/assessment.css', array('legacyx-polish'), file_exists($assessment) ? filemtime($assessment) : '1.0.0');
}
add_action('wp_enqueue_scripts','legacyx_enqueue_assets');

function legacyx_client_portal_template($template) {
    if (is_page('client-portal')) { $portal = get_theme_file_path('/client-portal.php'); if (file_exists($portal)) return $portal; }
    if (is_page('executive-assessment')) { $assessment = get_theme_file_path('/executive-assessment.php'); if (file_exists($assessment)) return $assessment; }
    return $template;
}
add_filter('template_include','legacyx_client_portal_template',99);

function legacyx_body_classes($classes) {
    if (is_page('client-portal')) $classes[] = 'legacyx-client-portal-page';
    if (is_page('executive-assessment')) $classes[] = 'legacyx-executive-assessment-page';
    return $classes;
}
add_filter('body_class','legacyx_body_classes');

function legacyx_register_assessment_type() {
    register_post_type('legacyx_assessment', array('labels'=>array('name'=>'Executive Assessments','singular_name'=>'Executive Assessment','menu_name'=>'Assessments','all_items'=>'All Assessments','view_item'=>'Review Assessment'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-clipboard','supports'=>array('title'),'capability_type'=>'post','map_meta_cap'=>true));
}
add_action('init','legacyx_register_assessment_type');

function legacyx_ensure_assessment_page() {
    if (get_option('legacyx_assessment_page_ready')) return;
    $page = get_page_by_path('executive-assessment');
    if (!$page) { $page_id=wp_insert_post(array('post_title'=>'Executive Assessment','post_name'=>'executive-assessment','post_status'=>'publish','post_type'=>'page','post_content'=>'')); if (!is_wp_error($page_id)&&$page_id) update_option('legacyx_assessment_page_ready',1,false); }
    else update_option('legacyx_assessment_page_ready',1,false);
}
add_action('init','legacyx_ensure_assessment_page',20);
function legacyx_clean_assessment_array($values){if(!is_array($values))return array();return array_values(array_filter(array_map('sanitize_text_field',$values)));}

function legacyx_submit_assessment() {
    if (!isset($_POST['legacyx_assessment_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['legacyx_assessment_nonce'])), 'legacyx_assessment_submit')) wp_die('Security validation failed. Please return to the assessment and try again.','Legacy X Firm',array('response'=>403));
    $full_name=isset($_POST['full_name'])?sanitize_text_field(wp_unslash($_POST['full_name'])):'';$email=isset($_POST['email'])?sanitize_email(wp_unslash($_POST['email'])):'';$timeline=isset($_POST['timeline'])?sanitize_text_field(wp_unslash($_POST['timeline'])):'';$objective=isset($_POST['objective'])?sanitize_textarea_field(wp_unslash($_POST['objective'])):'';$consent=!empty($_POST['consent']);
    if(!$full_name||!is_email($email)||!$timeline||!$objective||!$consent)wp_die('Please complete all required assessment fields.','Legacy X Firm',array('response'=>400));
    $assessment_id=wp_insert_post(array('post_type'=>'legacyx_assessment','post_status'=>'private','post_title'=>$full_name.' — '.current_time('Y-m-d H:i')));if(is_wp_error($assessment_id)||!$assessment_id)wp_die('We could not save your assessment. Please try again.','Legacy X Firm',array('response'=>500));
    $fields=array('full_name'=>$full_name,'email'=>$email,'phone'=>isset($_POST['phone'])?sanitize_text_field(wp_unslash($_POST['phone'])):'','location'=>isset($_POST['location'])?sanitize_text_field(wp_unslash($_POST['location'])):'','role'=>isset($_POST['role'])?sanitize_text_field(wp_unslash($_POST['role'])):'','business_name'=>isset($_POST['business_name'])?sanitize_text_field(wp_unslash($_POST['business_name'])):'','business_stage'=>isset($_POST['business_stage'])?sanitize_text_field(wp_unslash($_POST['business_stage'])):'','revenue'=>isset($_POST['revenue'])?sanitize_text_field(wp_unslash($_POST['revenue'])):'','capital_need'=>isset($_POST['capital_need'])?sanitize_text_field(wp_unslash($_POST['capital_need'])):'','timeline'=>$timeline,'objective'=>$objective,'obstacle'=>isset($_POST['obstacle'])?sanitize_textarea_field(wp_unslash($_POST['obstacle'])):'','engagement'=>isset($_POST['engagement'])?sanitize_text_field(wp_unslash($_POST['engagement'])):'','source'=>isset($_POST['source'])?sanitize_text_field(wp_unslash($_POST['source'])):'','priorities'=>legacyx_clean_assessment_array(isset($_POST['priorities'])?wp_unslash($_POST['priorities']):array()),'consent'=>1,'submitted_at'=>current_time('mysql'));
    foreach($fields as $key=>$value)update_post_meta($assessment_id,'_legacyx_'.$key,$value);
    $name_parts=preg_split('/\s+/',trim($full_name),2);$first_name=$name_parts[0]??'';$last_name=$name_parts[1]??'';
    $client_id=wp_insert_post(array('post_type'=>'legacyx_client','post_status'=>'publish','post_title'=>$full_name.($fields['business_name']?' — '.$fields['business_name']:'')));
    if(!is_wp_error($client_id)&&$client_id){$client_meta=array('first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email,'phone'=>$fields['phone'],'business_name'=>$fields['business_name'],'annual_revenue'=>$fields['revenue'],'capital_goal'=>$fields['capital_need'],'status'=>'Assessment','service_level'=>$fields['engagement'],'source'=>$fields['source']?:'Executive Assessment','assessment_id'=>$assessment_id,'ownership_notes'=>'Primary role: '.$fields['role']."\nBusiness stage: ".$fields['business_stage'],'financial_notes'=>'Primary objective: '.$objective."\nCurrent obstacle: ".$fields['obstacle']."\nTimeline: ".$timeline."\nPriorities: ".implode(', ',$fields['priorities']));foreach($client_meta as $key=>$value)update_post_meta($client_id,'_legacyx_'.$key,$value);update_post_meta($client_id,'_legacyx_client_id',legacyx_client_id($client_id));update_post_meta($assessment_id,'_legacyx_client_profile_id',$client_id);}
    wp_mail(get_option('admin_email'),'New Legacy X Firm Executive Assessment — '.$full_name,"A new Executive Assessment has been submitted.\n\nName: {$full_name}\nEmail: {$email}\nTimeline: {$timeline}\n\nReview it inside WordPress Admin > Assessments. A Universal Client Profile has also been created.");wp_safe_redirect(add_query_arg('submitted','1',home_url('/executive-assessment/')));exit;
}
add_action('admin_post_nopriv_legacyx_submit_assessment','legacyx_submit_assessment');add_action('admin_post_legacyx_submit_assessment','legacyx_submit_assessment');

function legacyx_assessment_columns($columns){return array('cb'=>$columns['cb']??'<input type="checkbox" />','title'=>'Principal','business'=>'Business','priority'=>'Primary Priorities','timeline'=>'Timeline','date'=>'Received');}add_filter('manage_legacyx_assessment_posts_columns','legacyx_assessment_columns');
function legacyx_assessment_column_content($column,$post_id){if($column==='business')echo esc_html(get_post_meta($post_id,'_legacyx_business_name',true));if($column==='priority'){$p=get_post_meta($post_id,'_legacyx_priorities',true);echo esc_html(is_array($p)?implode(', ',array_slice($p,0,2)):'');}if($column==='timeline')echo esc_html(get_post_meta($post_id,'_legacyx_timeline',true));}add_action('manage_legacyx_assessment_posts_custom_column','legacyx_assessment_column_content',10,2);
function legacyx_assessment_details_box(){add_meta_box('legacyx-assessment-details','Executive Assessment Details','legacyx_render_assessment_details','legacyx_assessment','normal','high');}add_action('add_meta_boxes','legacyx_assessment_details_box');
function legacyx_render_assessment_details($post){$labels=array('full_name'=>'Full Name','email'=>'Email','phone'=>'Phone','location'=>'Location','role'=>'Primary Role','business_name'=>'Business / Organization','business_stage'=>'Business Stage','revenue'=>'Approx. Annual Revenue','capital_need'=>'Capital Need / Objective','timeline'=>'Timeline','objective'=>'Primary Outcome','obstacle'=>'Current Obstacle','engagement'=>'Preferred Engagement','source'=>'Referral Source','priorities'=>'Strategic Priorities','submitted_at'=>'Submitted At');echo '<div style="display:grid;grid-template-columns:220px 1fr;gap:0;border:1px solid #ddd">';foreach($labels as $key=>$label){$value=get_post_meta($post->ID,'_legacyx_'.$key,true);if(is_array($value))$value=implode(', ',$value);echo '<div style="padding:10px 12px;font-weight:700;background:#f6f6f6;border-bottom:1px solid #ddd">'.esc_html($label).'</div><div style="padding:10px 12px;border-bottom:1px solid #ddd">'.nl2br(esc_html($value)).'</div>';}$client_profile_id=absint(get_post_meta($post->ID,'_legacyx_client_profile_id',true));if($client_profile_id)echo '<div style="padding:10px 12px;font-weight:700;background:#f6f6f6">Client Profile</div><div style="padding:10px 12px"><a href="'.esc_url(get_edit_post_link($client_profile_id)).'">Open Universal Client Profile</a></div>';echo '</div>';}

function legacyx_disable_emojis(){remove_action('wp_head','print_emoji_detection_script',7);remove_action('wp_print_styles','print_emoji_styles');remove_action('admin_print_scripts','print_emoji_detection_script');remove_action('admin_print_styles','print_emoji_styles');}add_action('init','legacyx_disable_emojis');
remove_action('wp_head','rsd_link');remove_action('wp_head','wlwmanifest_link');remove_action('wp_head','wp_generator');remove_action('wp_head','wp_shortlink_wp_head');remove_action('template_redirect','wp_shortlink_header',11);remove_action('wp_head','wp_oembed_add_discovery_links');
function legacyx_remove_embed(){if(!is_admin())wp_deregister_script('wp-embed');}add_action('wp_footer','legacyx_remove_embed',1);function legacyx_remove_dashicons(){if(!is_user_logged_in())wp_dequeue_style('dashicons');}add_action('wp_enqueue_scripts','legacyx_remove_dashicons',100);function legacyx_disable_self_pingbacks(&$links){$home=home_url();foreach($links as $key=>$link)if(strpos($link,$home)===0)unset($links[$key]);}add_action('pre_ping','legacyx_disable_self_pingbacks');function legacyx_async_images($attr){if(!is_admin()&&empty($attr['decoding']))$attr['decoding']='async';return $attr;}add_filter('wp_get_attachment_image_attributes','legacyx_async_images',20);
