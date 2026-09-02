<?php
if(!defined('ABSPATH'))exit;
require_once get_theme_file_path('/inc/applications.php');

function legacyx_register_workflow_items(){
 register_post_type('legacyx_work_item',array(
  'labels'=>array('name'=>'Workflow Items','singular_name'=>'Workflow Item','menu_name'=>'Documents & Tasks','add_new_item'=>'Add Document Request or Task','edit_item'=>'Edit Workflow Item'),
  'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-clipboard','supports'=>array('title','editor'),'show_in_rest'=>false
 ));
}
add_action('init','legacyx_register_workflow_items');

function legacyx_workflow_metabox(){add_meta_box('legacyx_workflow_details','Client Workflow Details','legacyx_render_workflow_metabox','legacyx_work_item','normal','high');}
add_action('add_meta_boxes','legacyx_workflow_metabox');

function legacyx_render_workflow_metabox($post){
 wp_nonce_field('legacyx_save_workflow','legacyx_workflow_nonce');
 $client=get_post_meta($post->ID,'_legacyx_client_profile_id',true);$type=get_post_meta($post->ID,'_legacyx_work_type',true);$status=get_post_meta($post->ID,'_legacyx_work_status',true);$due=get_post_meta($post->ID,'_legacyx_due_date',true);$priority=get_post_meta($post->ID,'_legacyx_priority',true);$advisor=get_post_meta($post->ID,'_legacyx_advisor_note',true);$doc=get_post_meta($post->ID,'_legacyx_document_url',true);
 $clients=get_posts(array('post_type'=>'legacyx_client','post_status'=>'publish','numberposts'=>-1,'orderby'=>'title','order'=>'ASC'));
 echo '<p><label style="display:block;font-weight:700;margin-bottom:6px">Client</label><select name="legacyx_work_client" style="width:100%"><option value="">Select client</option>';foreach($clients as $c)echo '<option value="'.esc_attr($c->ID).'" '.selected((string)$client,(string)$c->ID,false).'>'.esc_html($c->post_title.' · '.get_post_meta($c->ID,'_legacyx_client_id',true)).'</option>';echo '</select></p>';
 echo '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px">';
 echo '<p><label style="display:block;font-weight:700;margin-bottom:6px">Type</label><select name="legacyx_work_type" style="width:100%">';foreach(array('Task','Document Request','Document Available','Review Item') as $o)echo '<option '.selected($type,$o,false).'>'.esc_html($o).'</option>';echo '</select></p>';
 echo '<p><label style="display:block;font-weight:700;margin-bottom:6px">Status</label><select name="legacyx_work_status" style="width:100%">';foreach(array('Open','In Progress','Waiting on Client','Submitted','Under Review','Completed','Archived') as $o)echo '<option '.selected($status,$o,false).'>'.esc_html($o).'</option>';echo '</select></p>';
 echo '<p><label style="display:block;font-weight:700;margin-bottom:6px">Due Date</label><input type="date" name="legacyx_due_date" value="'.esc_attr($due).'" style="width:100%"></p>';
 echo '<p><label style="display:block;font-weight:700;margin-bottom:6px">Priority</label><select name="legacyx_priority" style="width:100%">';foreach(array('Normal','High','Urgent') as $o)echo '<option '.selected($priority,$o,false).'>'.esc_html($o).'</option>';echo '</select></p></div>';
 echo '<p><label style="display:block;font-weight:700;margin-bottom:6px">Advisor Next Action / Note</label><textarea name="legacyx_advisor_note" style="width:100%;min-height:90px">'.esc_textarea($advisor).'</textarea></p>';
 echo '<p><label style="display:block;font-weight:700;margin-bottom:6px">Approved Document URL (optional)</label><input type="url" name="legacyx_document_url" value="'.esc_attr($doc).'" style="width:100%"><small>Use only a protected or intentionally shareable file URL. Do not place private documents in the public GitHub repository.</small></p>';
}

function legacyx_save_workflow_item($id){
 if(get_post_type($id)!=='legacyx_work_item'||!isset($_POST['legacyx_workflow_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['legacyx_workflow_nonce'])),'legacyx_save_workflow')||!current_user_can('edit_post',$id))return;
 $map=array('client_profile_id'=>absint($_POST['legacyx_work_client']??0),'work_type'=>sanitize_text_field(wp_unslash($_POST['legacyx_work_type']??'Task')),'work_status'=>sanitize_text_field(wp_unslash($_POST['legacyx_work_status']??'Open')),'due_date'=>sanitize_text_field(wp_unslash($_POST['legacyx_due_date']??'')),'priority'=>sanitize_text_field(wp_unslash($_POST['legacyx_priority']??'Normal')),'advisor_note'=>sanitize_textarea_field(wp_unslash($_POST['legacyx_advisor_note']??'')),'document_url'=>esc_url_raw(wp_unslash($_POST['legacyx_document_url']??'')));
 foreach($map as $k=>$v)update_post_meta($id,'_legacyx_'.$k,$v);
}
add_action('save_post_legacyx_work_item','legacyx_save_workflow_item');

function legacyx_get_client_workflow($client_id){
 if(!$client_id)return array();
 return get_posts(array('post_type'=>'legacyx_work_item','post_status'=>'publish','numberposts'=>-1,'meta_key'=>'_legacyx_client_profile_id','meta_value'=>(string)absint($client_id),'orderby'=>'date','order'=>'DESC'));
}

function legacyx_workflow_counts($client_id){$items=legacyx_get_client_workflow($client_id);$out=array('open'=>0,'documents'=>0,'completed'=>0);foreach($items as $item){$s=get_post_meta($item->ID,'_legacyx_work_status',true);$t=get_post_meta($item->ID,'_legacyx_work_type',true);if($s==='Completed')$out['completed']++;else if($s!=='Archived')$out['open']++;if(strpos($t,'Document')!==false&&$s!=='Archived')$out['documents']++;}return $out;}

function legacyx_applications_template_router($template){if(is_page('applications-center')){$f=get_theme_file_path('/applications-center.php');if(file_exists($f))return $f;}return $template;}add_filter('template_include','legacyx_applications_template_router',100);
function legacyx_ensure_applications_page(){if(!get_page_by_path('applications-center'))wp_insert_post(array('post_title'=>'Applications Center','post_name'=>'applications-center','post_status'=>'publish','post_type'=>'page','post_content'=>''));}add_action('init','legacyx_ensure_applications_page',25);
